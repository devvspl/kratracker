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
            'to'      => 'required|array|min:1',
            'to.*'    => 'email',
            'cc'      => 'nullable|array',
            'cc.*'    => 'email',
            'bcc'     => 'nullable|array',
            'bcc.*'   => 'email',
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $appName = config('app.name', 'Performia');
        $sender  = auth()->user();

        // Body is already HTML from TinyMCE
        $html = "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;'>
        <div style='max-width:600px;margin:24px auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'>
          <div style='background:#0d9488;padding:18px 24px;'>
            <div style='color:#fff;font-size:17px;font-weight:700;'>{$appName}</div>
            <div style='color:#99f6e4;font-size:11px;margin-top:2px;'>Performance Management System</div>
          </div>
          <div style='padding:24px;color:#334155;font-size:14px;line-height:1.7;'>
            {$validated['body']}
          </div>
          <div style='padding:12px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:10px;color:#94a3b8;'>
            Sent by {$sender->name} via {$appName}
          </div>
        </div></body></html>";

        $toList  = $validated['to'];
        $ccList  = $validated['cc']  ?? [];
        $bccList = $validated['bcc'] ?? [];
        $subject = $validated['subject'];

        // Build name map from contacts + users
        $nameMap = [];
        EmailContact::all()->each(fn($c) => $nameMap[$c->email] = $c->name);
        \App\Models\User::all()->each(fn($u) => $nameMap[$u->email] = $u->name);

        try {
            Mail::send([], [], function (Message $mail) use ($toList, $ccList, $bccList, $subject, $html, $nameMap) {
                foreach ($toList as $email) {
                    $mail->to($email, $nameMap[$email] ?? $email);
                }
                foreach ($ccList as $email) {
                    $mail->cc($email, $nameMap[$email] ?? $email);
                }
                foreach ($bccList as $email) {
                    $mail->bcc($email, $nameMap[$email] ?? $email);
                }
                $mail->subject($subject)->html($html);
            });

            $count = count($toList) + count($ccList) + count($bccList);
            return response()->json(['success' => true, 'message' => "Email sent to {$count} recipient(s)."]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }

    /** Send a report to a contact */
    public function sendReport(Request $request, ReportService $reporter)
    {
        $validated = $request->validate([
            'contact_id'    => 'required|exists:email_contacts,id',
            'report_type'   => 'required|in:daily,weekly,monthly',
            'report_format' => 'nullable|in:email,pdf,excel',
            'date_from'     => 'nullable|date',
            'date_to'       => 'nullable|date',
        ]);

        $contact = EmailContact::findOrFail($validated['contact_id']);
        $format  = $validated['report_format'] ?? 'email';
        $type    = $validated['report_type'];
        $from    = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : now()->startOfMonth();
        $to      = isset($validated['date_to'])   ? Carbon::parse($validated['date_to'])   : now();

        // Use the logged-in user as the "employee" (report is about auth user's logs)
        $employee = auth()->user();

        // Build a pseudo-user for the contact recipient
        $recipient        = new \App\Models\User();
        $recipient->name  = $contact->name;
        $recipient->email = $contact->email;

        $appName = config('app.name', 'Performia');
        $subject = ucfirst($type) . " Report — {$from->format('d M')} to {$to->format('d M Y')} | {$appName}";

        try {
            if ($format === 'email') {
                // Plain HTML email
                $ok = $reporter->sendReport($recipient, $employee, $type, $from, $to);
            } elseif ($format === 'pdf') {
                // Generate PDF and attach
                $logs = WorkLog::with(['subKra.kra', 'application', 'module', 'priority', 'status'])
                    ->where('user_id', $employee->id)
                    ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
                    ->latest('log_date')->get();

                $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.analytics-pdf', [
                    'workLogs'   => $logs,
                    'dateFrom'   => $from,
                    'dateTo'     => $to,
                    'user'       => $employee,
                    'reportType' => $type,
                    'date'       => now()->format('d M Y, H:i'),
                ]);
                $pdfContent = $pdf->output();

                Mail::send([], [], function (Message $mail) use ($contact, $subject, $pdfContent, $appName, $from, $to, $type) {
                    $mail->to($contact->email, $contact->name)
                         ->subject($subject)
                         ->html("<p>Hi {$contact->name},</p><p>Please find the attached {$type} report for {$from->format('d M')} – {$to->format('d M Y')}.</p><p>Regards,<br>{$appName}</p>")
                         ->attachData($pdfContent, "report_{$type}_{$from->format('Y-m-d')}.pdf", ['mime' => 'application/pdf']);
                });
                $ok = true;
            } elseif ($format === 'excel') {
                // Generate Excel and attach
                $logs2    = WorkLog::with(['subKra.kra', 'application', 'module', 'priority', 'status'])
                    ->where('user_id', $employee->id)
                    ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
                    ->latest('log_date')->get();

                $export   = new \App\Exports\WorkLogsExport($logs2);
                $fileName = "report_{$type}_{$from->format('Y-m-d')}.xlsx";
                $tmpKey   = 'tmp_' . $fileName;

                \Maatwebsite\Excel\Facades\Excel::store($export, $tmpKey, 'local');
                $tmpPath  = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . $tmpKey);

                Mail::send([], [], function (Message $mail) use ($contact, $subject, $tmpPath, $fileName, $appName, $from, $to, $type) {
                    $mail->to($contact->email, $contact->name)
                         ->subject($subject)
                         ->html("<p>Hi {$contact->name},</p><p>Please find the attached {$type} report for {$from->format('d M')} – {$to->format('d M Y')}.</p><p>Regards,<br>{$appName}</p>")
                         ->attach($tmpPath, ['as' => $fileName, 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
                });

                if (file_exists($tmpPath)) @unlink($tmpPath);
                $ok = true;
            } else {
                $ok = false;
            }
        } catch (\Throwable $e) {
            \Log::error('sendReport failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => $ok,
            'message' => $ok ? "Report sent to {$contact->email} as " . strtoupper($format) . "." : 'Failed to send report.',
        ]);
    }
}
