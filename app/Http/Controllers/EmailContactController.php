<?php

namespace App\Http\Controllers;

use App\Models\EmailContact;
use App\Models\WorkLog;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class EmailContactController extends Controller
{
    public function index()
    {
        $contacts = EmailContact::with('creator')->latest()->get();
        return response()->json($contacts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'email'                    => 'required|email|max:255',
            'role'                     => 'nullable|string|max:100',
            'notes'                    => 'nullable|string',
            'notify_on_complete'       => 'boolean',
            'notify_on_status_change'  => 'boolean',
            'notify_on_daily_report'   => 'boolean',
            'notify_on_weekly_report'  => 'boolean',
            'notify_on_monthly_report' => 'boolean',
        ]);

        $contact = EmailContact::create([...$validated, 'created_by' => auth()->id()]);
        return response()->json(['success' => true, 'message' => 'Contact added.', 'data' => $contact]);
    }

    public function update(Request $request, EmailContact $emailContact)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'email'                    => 'required|email|max:255',
            'role'                     => 'nullable|string|max:100',
            'notes'                    => 'nullable|string',
            'notify_on_complete'       => 'boolean',
            'notify_on_status_change'  => 'boolean',
            'notify_on_daily_report'   => 'boolean',
            'notify_on_weekly_report'  => 'boolean',
            'notify_on_monthly_report' => 'boolean',
            'is_active'                => 'boolean',
        ]);

        $emailContact->update($validated);
        return response()->json(['success' => true, 'message' => 'Contact updated.', 'data' => $emailContact]);
    }

    public function destroy(EmailContact $emailContact)
    {
        $emailContact->delete();
        return response()->json(['success' => true, 'message' => 'Contact deleted.']);
    }

    /** Send a custom one-off email to a contact */
    public function sendCustom(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:email_contacts,id',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
        ]);

        $contact = EmailContact::findOrFail($validated['contact_id']);
        $appName = config('app.name', 'Performia');
        $sender  = auth()->user();

        $html = "
        <!DOCTYPE html><html><body style='margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;'>
        <div style='max-width:560px;margin:24px auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'>
          <div style='background:#0d9488;padding:18px 24px;'>
            <div style='color:#fff;font-size:17px;font-weight:700;'>{$appName}</div>
            <div style='color:#99f6e4;font-size:11px;margin-top:2px;'>Performance Management System</div>
          </div>
          <div style='padding:24px;'>
            <p style='color:#334155;font-size:14px;line-height:1.7;white-space:pre-line;'>{$validated['body']}</p>
          </div>
          <div style='padding:12px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:10px;color:#94a3b8;'>
            Sent by {$sender->name} via {$appName}
          </div>
        </div></body></html>";

        try {
            Mail::send([], [], function (Message $mail) use ($contact, $validated, $html) {
                $mail->to($contact->email, $contact->name)
                     ->subject($validated['subject'])
                     ->html($html);
            });
            return response()->json(['success' => true, 'message' => "Email sent to {$contact->email}."]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }

    /** Send a report to a contact */
    public function sendReport(Request $request, ReportService $reporter)
    {
        $validated = $request->validate([
            'contact_id'  => 'required|exists:email_contacts,id',
            'employee_id' => 'required|exists:users,id',
            'report_type' => 'required|in:daily,weekly,monthly',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date',
        ]);

        $contact  = EmailContact::findOrFail($validated['contact_id']);
        $employee = \App\Models\User::findOrFail($validated['employee_id']);
        $type     = $validated['report_type'];
        $from     = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : null;
        $to       = isset($validated['date_to'])   ? Carbon::parse($validated['date_to'])   : null;

        // Build a pseudo-user for the contact recipient
        $recipientPseudo        = new \App\Models\User();
        $recipientPseudo->name  = $contact->name;
        $recipientPseudo->email = $contact->email;

        $ok = $reporter->sendReport($recipientPseudo, $employee, $type, $from, $to);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? "Report sent to {$contact->email}." : 'Failed to send report.',
        ]);
    }
}
