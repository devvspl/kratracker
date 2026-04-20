<?php

namespace App\Http\Controllers;

use App\Models\ReportConfig;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $userId    = auth()->id();
        $configs   = ReportConfig::with(['recipient', 'employee'])->where('created_by', $userId)->latest()->get();
        $contacts  = \App\Models\EmailContact::with('creator')->where('created_by', $userId)->latest()->get();
        $users     = User::with('roles')->orderBy('name')->get();
        $employees = User::role(['Employee', 'Manager'])->orderBy('name')->get();
        return view('reports.index', compact('configs', 'contacts', 'users', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_user_id' => 'required|exists:users,id',
            'employee_user_id'  => 'nullable|exists:users,id',
            'report_type'       => 'required|in:daily,weekly,monthly',
        ]);

        $exists = ReportConfig::where('recipient_user_id', $validated['recipient_user_id'])
            ->where('employee_user_id', $validated['employee_user_id'] ?? null)
            ->where('report_type', $validated['report_type'])
            ->where('created_by', auth()->id())
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This report config already exists.'], 422);
        }

        $config = ReportConfig::create([...$validated, 'created_by' => auth()->id()]);
        return response()->json(['success' => true, 'message' => 'Report config created.', 'data' => $config->load(['recipient', 'employee'])]);
    }

    public function update(Request $request, ReportConfig $reportConfig)
    {
        $validated = $request->validate([
            'recipient_user_id' => 'required|exists:users,id',
            'employee_user_id'  => 'nullable|exists:users,id',
            'report_type'       => 'required|in:daily,weekly,monthly',
            'is_active'         => 'boolean',
        ]);
        $reportConfig->update($validated);
        return response()->json(['success' => true, 'message' => 'Report config updated.']);
    }

    public function destroy(ReportConfig $reportConfig)
    {
        $reportConfig->delete();
        return response()->json(['success' => true, 'message' => 'Report config deleted.']);
    }

    /** Manual send — trigger immediately */
    public function sendNow(Request $request, ReportService $reporter)
    {
        $validated = $request->validate([
            'recipient_user_id' => 'required|exists:users,id',
            'employee_user_id'  => 'nullable|exists:users,id',
            'report_type'       => 'required|in:daily,weekly,monthly',
            'date_from'         => 'nullable|date',
            'date_to'           => 'nullable|date',
        ]);

        $recipient = User::findOrFail($validated['recipient_user_id']);
        $type      = $validated['report_type'];
        $from      = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : null;
        $to        = isset($validated['date_to'])   ? Carbon::parse($validated['date_to'])   : null;

        $employees = isset($validated['employee_user_id'])
            ? collect([User::findOrFail($validated['employee_user_id'])])
            : User::role(['Employee', 'Manager'])->get();

        $sent = 0;
        foreach ($employees as $employee) {
            if ($reporter->sendReport($recipient, $employee, $type, $from, $to)) $sent++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$sent} report(s) sent to {$recipient->email}.",
        ]);
    }
}
