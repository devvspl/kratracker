<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\WorkLogFeedback;
use App\Models\SubKra;
use App\Models\Application;
use App\Models\ApplicationModule;
use App\Models\Priority;
use App\Models\TaskStatus;
use App\Models\PeriodTarget;
use App\Models\EmailContact;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkLog::with(['subKra.kra', 'application', 'priority', 'status'])
            ->where('user_id', auth()->id());

        $dateFrom = $request->has('date_from') ? $request->date_from : date('Y-m-d');
        $dateTo   = $request->has('date_to') ? $request->date_to : date('Y-m-d');

        if (!empty($dateFrom)) $query->where('log_date', '>=', $dateFrom);
        if (!empty($dateTo))   $query->where('log_date', '<=', $dateTo);
        if ($request->filled('sub_kra_id')) $query->where('sub_kra_id', $request->sub_kra_id);
        if ($request->filled('status_id'))  $query->where('status_id', $request->status_id);
        if ($request->filled('priority_id'))$query->where('priority_id', $request->priority_id);
        if ($request->filled('test_status'))$query->where('test_status', $request->test_status);
        if ($request->filled('application_id')) $query->where('application_id', $request->application_id);
        if ($request->filled('module_id'))      $query->where('module_id', $request->module_id);

        $workLogs    = $query->latest('log_date')->paginate(20);
        $subKras     = SubKra::with('kra')->where('is_active', true)->get();
        $applications= Application::where('is_active', true)->get();
        $priorities  = Priority::where('is_active', true)->orderBy('level', 'desc')->get();
        $statuses    = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();
        $modules     = ApplicationModule::where('is_active', true)->orderBy('name')->get();
        $contacts    = EmailContact::where('is_active', true)->orderBy('name')->get(['id','name','email']);

        return view('work-logs.index', compact('workLogs', 'subKras', 'applications', 'priorities', 'statuses', 'modules', 'contacts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_kra_id'       => 'required|exists:sub_kras,id',
            'application_id'   => 'nullable|exists:applications,id',
            'module_id'        => 'nullable|exists:application_modules,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'log_date'         => 'required|date',
            'priority_id'      => 'nullable|exists:priorities,id',
            'status_id'        => 'required|exists:task_statuses,id',
            'achievement_value'=> 'nullable|numeric|min:0',
            'total_duration'   => 'nullable|numeric|min:0',
            'actual_duration'  => 'nullable|numeric|min:0',
            'test_status'      => 'nullable|string|max:100',
            'testing_details'  => 'nullable|string',
            'remark'           => 'nullable|string',
            'notify_contact_ids' => 'nullable|array',
            'notify_contact_ids.*' => 'exists:email_contacts,id',
        ]);

        // Get target value from period targets
        $subKra  = SubKra::findOrFail($validated['sub_kra_id']);
        $logDate = Carbon::parse($validated['log_date']);
        $validated['log_date'] = $logDate->format('Y-m-d');

        $periodTarget = PeriodTarget::where('sub_kra_id', $subKra->id)
            ->where('period_year', $logDate->year)
            ->where(function ($q) use ($subKra, $logDate) {
                if ($subKra->review_period === 'Monthly') {
                    $q->where('period_month_or_quarter', $logDate->month);
                } elseif ($subKra->review_period === 'Quarterly') {
                    $q->where('period_month_or_quarter', (int) ceil($logDate->month / 3));
                }
            })->first();

        $targetValue = $periodTarget ? $periodTarget->target_value : 100;

        $workLog = WorkLog::create([
            'user_id'              => auth()->id(),
            'sub_kra_id'           => $validated['sub_kra_id'],
            'application_id'       => $validated['application_id'] ?? null,
            'module_id'            => $validated['module_id'] ?? null,
            'title'                => $validated['title'],
            'description'          => $validated['description'] ?? null,
            'log_date'             => $validated['log_date'],
            'priority_id'          => $validated['priority_id'],
            'status_id'            => $validated['status_id'],
            'achievement_value'    => $validated['achievement_value'] ?? 1,
            'target_value_snapshot'=> $targetValue,
            'total_duration'       => $validated['total_duration'] ?? 0,
            'actual_duration'      => $validated['actual_duration'] ?? 0,
            'duration_difference'  => ($validated['total_duration'] ?? 0) - ($validated['actual_duration'] ?? 0),
            'test_status'          => $validated['test_status'] ?? null,
            'testing_details'      => $validated['testing_details'] ?? null,
            'remark'               => $validated['remark'] ?? null,
        ]);

        $workLog->calculateScore();

        app(NotificationService::class)->notify(
            auth()->user(), 'task_created',
            "Work log \"{$workLog->title}\" created successfully.",
            ['work_log_id' => $workLog->id, 'title' => $workLog->title]
        );

        // Notify selected external contacts
        if (!empty($validated['notify_contact_ids'])) {
            $this->notifyContacts($workLog->fresh(['subKra.kra', 'status', 'priority']), 'complete', optional($workLog->status)->name ?? 'Created', $validated['notify_contact_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Work log created successfully',
            'data'    => $workLog->load(['subKra.kra', 'application', 'priority', 'status']),
        ]);
    }

    public function show(WorkLog $workLog)
    {
        $workLog->load(['subKra.kra', 'subKra.logic', 'application', 'priority', 'status', 'feedbacks.user']);

        return response()->json([
            'success' => true,
            'data'    => $workLog,
        ]);
    }

    public function update(Request $request, WorkLog $workLog)
    {
        if ($workLog->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'sub_kra_id'       => 'required|exists:sub_kras,id',
            'application_id'   => 'nullable|exists:applications,id',
            'module_id'        => 'nullable|exists:application_modules,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'log_date'         => 'required|date',
            'priority_id'      => 'nullable|exists:priorities,id',
            'status_id'        => 'required|exists:task_statuses,id',
            'achievement_value'=> 'nullable|numeric|min:0',
            'total_duration'   => 'nullable|numeric|min:0',
            'actual_duration'  => 'nullable|numeric|min:0',
            'test_status'      => 'nullable|string|max:100',
            'testing_details'  => 'nullable|string',
            'remark'           => 'nullable|string',
            'notify_contact_ids'   => 'nullable|array',
            'notify_contact_ids.*' => 'exists:email_contacts,id',
        ]);

        if (isset($validated['log_date'])) {
            $validated['log_date'] = Carbon::parse($validated['log_date'])->format('Y-m-d');
        }

        $totalDur = $validated['total_duration'] ?? $workLog->total_duration ?? 0;
        $actualDur = $validated['actual_duration'] ?? $workLog->actual_duration ?? 0;
        $validated['duration_difference'] = $totalDur - $actualDur;

        $workLog->update($validated);
        $workLog->calculateScore();

        // Notify if marked completed
        $newStatus = optional($workLog->fresh()->status)->name ?? '';
        if (str_contains($newStatus, 'Completed')) {
            app(NotificationService::class)->notify(
                auth()->user(), 'task_completed',
                "Great work! Task \"{$workLog->title}\" has been marked as Completed.",
                ['work_log_id' => $workLog->id, 'title' => $workLog->title]
            );
            $this->notifyContacts($workLog, 'complete', $newStatus, $validated['notify_contact_ids'] ?? []);
        } else {
            $this->notifyContacts($workLog, 'status_change', $newStatus, $validated['notify_contact_ids'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Work log updated successfully',
            'data'    => $workLog->load(['subKra.kra', 'application', 'priority', 'status']),
        ]);
    }

    public function destroy(WorkLog $workLog)
    {
        if ($workLog->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($workLog->attachments) {
            foreach ($workLog->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $workLog->delete();

        return response()->json(['success' => true, 'message' => 'Work log deleted successfully']);
    }

    private function notifyContacts(WorkLog $workLog, string $event, string $statusName, array $specificIds = []): void
    {
        if (!empty($specificIds)) {
            $contacts = \App\Models\EmailContact::whereIn('id', $specificIds)->where('is_active', true)->get();
        } else {
            $field    = $event === 'complete' ? 'notify_on_complete' : 'notify_on_status_change';
            $contacts = \App\Models\EmailContact::where($field, true)->where('is_active', true)->get();
        }

        if ($contacts->isEmpty()) return;

        $appName  = config('app.name', 'KRA Tracker');
        $appUrl   = rtrim(config('app.url'), '/');
        $user     = auth()->user();
        $subject  = $event === 'complete'
            ? "✅ Task Completed — {$workLog->title}"
            : "🔄 Task Status Updated — {$workLog->title}";

        $kra    = optional($workLog->subKra->kra)->name ?? '—';
        $subKra = optional($workLog->subKra)->name ?? '—';
        $score  = number_format($workLog->score_calculated, 1);
        $dur    = ($workLog->actual_duration ?? 0) . 'h';

        $html = "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;'>
        <div style='max-width:540px;margin:24px auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'>
          <div style='background:#0d9488;padding:18px 24px;'>
            <div style='color:#fff;font-size:17px;font-weight:700;'>{$appName}</div>
            <div style='color:#99f6e4;font-size:11px;margin-top:2px;'>Task Status Notification</div>
          </div>
          <div style='padding:20px 24px;'>
            <h2 style='margin:0 0 12px;color:#1e293b;font-size:15px;'>{$subject}</h2>
            <table style='width:100%;border-collapse:collapse;font-size:12px;'>
              <tr><td style='padding:6px 0;color:#64748b;width:120px;'>Employee</td><td style='padding:6px 0;font-weight:600;color:#334155;'>{$user->name}</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;'>Task</td><td style='padding:6px 0;font-weight:600;color:#334155;'>{$workLog->title}</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;'>KRA / Sub-KRA</td><td style='padding:6px 0;color:#334155;'>{$kra} › {$subKra}</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;'>Status</td><td style='padding:6px 0;'><span style='background:#dcfce7;color:#166534;padding:2px 8px;border-radius:10px;font-weight:600;font-size:11px;'>{$statusName}</span></td></tr>
              <tr><td style='padding:6px 0;color:#64748b;'>Score</td><td style='padding:6px 0;font-weight:700;color:#0d9488;'>{$score}%</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;'>Duration</td><td style='padding:6px 0;color:#334155;'>{$dur}</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;'>Date</td><td style='padding:6px 0;color:#334155;'>{$workLog->log_date->format('d M Y')}</td></tr>
            </table>
          </div>
          <div style='padding:12px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:10px;color:#94a3b8;'>
            Automated notification from {$appName}
          </div>
        </div></body></html>";

        foreach ($contacts as $contact) {
            try {
                \Mail::send([], [], function (\Illuminate\Mail\Message $mail) use ($contact, $subject, $html) {
                    $mail->to($contact->email, $contact->name)->subject($subject)->html($html);
                });
            } catch (\Throwable $e) {
                \Log::error("Contact notify failed to {$contact->email}: " . $e->getMessage());
            }
        }
    }

    public function storeFeedback(Request $request, WorkLog $workLog)
    {
        $validated = $request->validate([
            'feedback_type' => 'required|in:self,manager',
            'comment'       => 'required|string',
            'rating'        => 'required|integer|min:1|max:5',
        ]);

        $feedback = WorkLogFeedback::create([
            'work_log_id'   => $workLog->id,
            'user_id'       => auth()->id(),
            'feedback_type' => $validated['feedback_type'],
            'comment'       => $validated['comment'],
            'rating'        => $validated['rating'],
        ]);

        // Notify the work log owner (if feedback is from someone else)
        if ($workLog->user_id !== auth()->id()) {
            app(NotificationService::class)->notify(
                $workLog->user,
                'feedback_added',
                auth()->user()->name . " added a {$validated['feedback_type']} feedback (rating: {$validated['rating']}/5) on your task \"{$workLog->title}\".",
                ['work_log_id' => $workLog->id, 'title' => $workLog->title, 'rating' => $validated['rating']]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Feedback added successfully',
            'data'    => $feedback->load('user'),
        ]);
    }
}
