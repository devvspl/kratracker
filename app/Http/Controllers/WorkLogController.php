<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationModule;
use App\Models\EmailContact;
use App\Models\PeriodTarget;
use App\Models\Priority;
use App\Models\SubKra;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogAttachment;
use App\Models\WorkLogFeedback;
use App\Models\WorkLogLink;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkLog::with(['subKra.kra', 'application', 'priority', 'status', 'attachments', 'links'])->where('user_id', auth()->id());

        $dateFrom = $request->has('date_from') ? $request->date_from : date('Y-m-d');
        $dateTo = $request->has('date_to') ? $request->date_to : date('Y-m-d');

        if (!empty($dateFrom))
            $query->where('log_date', '>=', $dateFrom);
        if (!empty($dateTo))
            $query->where('log_date', '<=', $dateTo);
        if ($request->filled('sub_kra_id'))
            $query->where('sub_kra_id', $request->sub_kra_id);
        if ($request->filled('status_id'))
            $query->where('status_id', $request->status_id);
        if ($request->filled('priority_id'))
            $query->where('priority_id', $request->priority_id);
        if ($request->filled('test_status'))
            $query->where('test_status', $request->test_status);
        if ($request->filled('application_id'))
            $query->where('application_id', $request->application_id);
        if ($request->filled('module_id'))
            $query->where('module_id', $request->module_id);
        if ($request->filled('has_attachment') && in_array($request->has_attachment, ['1', 'true', true], true)) {
            $query->whereHas('attachments');
        }
        if ($request->filled('has_link') && in_array($request->has_link, ['1', 'true', true], true)) {
            $query->whereHas('links');
        }

        $workLogs = $query->latest('log_date')->paginate(20);
        $isAdmin = auth()->user()->hasRole('Admin');
        $subKras = SubKra::with('kra')->where('is_active', true)->whereHas('kra', fn($q) => $isAdmin ? $q : $q->ownedByUser())->get();
        $applications = ($isAdmin ? Application::query() : Application::ownedByUser())->where('is_active', true)->orderBy('name')->get();
        $priorities = ($isAdmin ? Priority::query() : Priority::ownedByUser())->orderBy('name')->get();
        $statuses = ($isAdmin ? TaskStatus::query() : TaskStatus::ownedByUser())->orderBy('sort_order')->get();
        $modules = ($isAdmin ? ApplicationModule::query() : ApplicationModule::ownedByUser())->where('is_active', true)->orderBy('name')->get();
        $contacts = EmailContact::where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']);
        $notifyUsers = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('work-logs.index', compact('workLogs', 'subKras', 'applications', 'priorities', 'statuses', 'modules', 'contacts', 'notifyUsers'));
    }

    public function sendCustomEmail(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:email_contacts,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $appName = config('app.name', 'Performia');
        $sender = auth()->user();
        $html = "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;'>
        <div style='max-width:540px;margin:24px auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'>
          <div style='background:#0d9488;padding:18px 24px;'>
            <div style='color:#fff;font-size:17px;font-weight:700;'>{$appName}</div>
            <div style='color:#99f6e4;font-size:11px;margin-top:2px;'>Message from {$sender->name}</div>
          </div>
          <div style='padding:24px;'>
            <p style='color:#334155;font-size:13px;line-height:1.8;white-space:pre-line;'>" . nl2br(e($validated['body'])) . "</p>
          </div>
          <div style='padding:10px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:10px;color:#94a3b8;'>
            Sent by {$sender->name} via {$appName}
          </div>
        </div></body></html>";

        $sent = 0;

        // Send to external contacts
        if (!empty($validated['contact_ids'])) {
            $contacts = \App\Models\EmailContact::whereIn('id', $validated['contact_ids'])->get();
            foreach ($contacts as $contact) {
                try {
                    \Mail::send([], [], function (\Illuminate\Mail\Message $mail) use ($contact, $validated, $html) {
                        $mail->to($contact->email, $contact->name)->subject($validated['subject'])->html($html);
                    });
                    $sent++;
                } catch (\Throwable $e) {
                    \Log::error("Custom email failed to {$contact->email}: " . $e->getMessage());
                }
            }
        }

        // Send to system users
        if (!empty($validated['user_ids'])) {
            $users = \App\Models\User::whereIn('id', $validated['user_ids'])->get();
            foreach ($users as $user) {
                try {
                    \Mail::send([], [], function (\Illuminate\Mail\Message $mail) use ($user, $validated, $html) {
                        $mail->to($user->email, $user->name)->subject($validated['subject'])->html($html);
                    });
                    $sent++;
                } catch (\Throwable $e) {
                    \Log::error("Custom email failed to {$user->email}: " . $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true, 'message' => "Email sent to {$sent} recipient(s)."]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_kra_id' => 'required|exists:sub_kras,id',
            'application_id' => 'nullable|exists:applications,id',
            'module_id' => 'nullable|exists:application_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'log_date' => 'required|date',
            'priority_id' => 'nullable|exists:priorities,id',
            'status_id' => 'nullable|exists:task_statuses,id',
            'achievement_value' => 'nullable|numeric|min:0',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'total_duration' => 'nullable|numeric|min:0',
            'actual_duration' => 'nullable|numeric|min:0',
            'test_status' => 'nullable|string|max:100',
            'testing_details' => 'nullable|string',
            'remark' => 'nullable|string',
            'notify_contact_ids' => 'nullable|array',
            'notify_contact_ids.*' => 'exists:email_contacts,id',
            'notify_user_ids' => 'nullable|array',
            'notify_user_ids.*' => 'exists:users,id',
            'links' => 'nullable|array',
            'links.*.title' => 'required|string|max:255',
            'links.*.url' => 'required|url|max:500',
        ]);

        // Handle file uploads
        $request->validate([
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif,zip,rar',
        ]);

        // Get target value from period targets
        $subKra = SubKra::findOrFail($validated['sub_kra_id']);
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
            })
            ->first();

        $targetValue = $periodTarget ? $periodTarget->target_value : 100;

        $workLog = WorkLog::create([
            'user_id' => auth()->id(),
            'sub_kra_id' => $validated['sub_kra_id'],
            'application_id' => $validated['application_id'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'log_date' => $validated['log_date'],
            'priority_id' => $validated['priority_id'],
            'status_id' => $validated['status_id'],
            'achievement_value' => $validated['achievement_value'] ?? 1,
            'target_value_snapshot' => $targetValue,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'total_duration' => $validated['total_duration'] ?? 0,
            'actual_duration' => $validated['actual_duration'] ?? 0,
            'duration_difference' => ($validated['total_duration'] ?? 0) - ($validated['actual_duration'] ?? 0),
            'test_status' => $validated['test_status'] ?? null,
            'testing_details' => $validated['testing_details'] ?? null,
            'remark' => $validated['remark'] ?? null,
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('work-log-attachments', $fileName, 'public');

                $workLog->attachments()->create([
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Handle links
        if (!empty($validated['links'])) {
            foreach ($validated['links'] as $link) {
                $workLog->links()->create([
                    'title' => $link['title'],
                    'url' => $link['url'],
                ]);
            }
        }

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

        // Notify selected system users via email
        if (!empty($validated['notify_user_ids'])) {
            $this->notifySystemUsers($workLog->fresh(['subKra.kra', 'status', 'priority']), $validated['notify_user_ids'], 'Created');
        }

        return response()->json([
            'success' => true,
            'message' => 'Work log created successfully',
            'data' => $workLog->load(['subKra.kra', 'application', 'priority', 'status', 'attachments', 'links']),
        ]);
    }

    public function show(WorkLog $workLog)
    {
        $workLog->load(['subKra.kra', 'subKra.logic', 'application', 'priority', 'status', 'feedbacks.user', 'attachments', 'links']);

        return response()->json([
            'success' => true,
            'data' => $workLog,
        ]);
    }

    public function update(Request $request, WorkLog $workLog)
    {
        if ($workLog->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'sub_kra_id' => 'required|exists:sub_kras,id',
            'application_id' => 'nullable|exists:applications,id',
            'module_id' => 'nullable|exists:application_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'log_date' => 'required|date',
            'priority_id' => 'nullable|exists:priorities,id',
            'status_id' => 'nullable|exists:task_statuses,id',
            'achievement_value' => 'nullable|numeric|min:0',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'total_duration' => 'nullable|numeric|min:0',
            'actual_duration' => 'nullable|numeric|min:0',
            'test_status' => 'nullable|string|max:100',
            'testing_details' => 'nullable|string',
            'remark' => 'nullable|string',
            'notify_contact_ids' => 'nullable|array',
            'notify_contact_ids.*' => 'exists:email_contacts,id',
            'notify_user_ids' => 'nullable|array',
            'notify_user_ids.*' => 'exists:users,id',
            'links' => 'nullable|array',
            'links.*.title' => 'required|string|max:255',
            'links.*.url' => 'required|url|max:500',
        ]);

        // Handle file uploads
        $request->validate([
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif,zip,rar',
        ]);

        if (isset($validated['log_date'])) {
            $validated['log_date'] = Carbon::parse($validated['log_date'])->format('Y-m-d');
        }

        $totalDur = $validated['total_duration'] ?? $workLog->total_duration ?? 0;
        $actualDur = $validated['actual_duration'] ?? $workLog->actual_duration ?? 0;
        $validated['duration_difference'] = $totalDur - $actualDur;

        $workLog->update($validated);

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('work-log-attachments', $fileName, 'public');

                $workLog->attachments()->create([
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Sync links — delete old, insert new
        if ($request->has('links')) {
            $workLog->links()->delete();
            foreach ($validated['links'] ?? [] as $link) {
                $workLog->links()->create([
                    'title' => $link['title'],
                    'url' => $link['url'],
                ]);
            }
        }

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

        if (!empty($validated['notify_user_ids'])) {
            $this->notifySystemUsers($workLog, $validated['notify_user_ids'], $newStatus);
        }

        return response()->json([
            'success' => true,
            'message' => 'Work log updated successfully',
            'data' => $workLog->load(['subKra.kra', 'application', 'priority', 'status', 'attachments', 'links']),
        ]);
    }

    public function destroy(WorkLog $workLog)
    {
        if ($workLog->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete physical files from storage
        foreach ($workLog->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $workLog->delete();  // cascade deletes attachments & links rows

        return response()->json(['success' => true, 'message' => 'Work log deleted successfully']);
    }

    private function notifyContacts(WorkLog $workLog, string $event, string $statusName, array $specificIds = []): void
    {
        if (!empty($specificIds)) {
            $contacts = \App\Models\EmailContact::whereIn('id', $specificIds)->where('is_active', true)->get();
        } else {
            $field = $event === 'complete' ? 'notify_on_complete' : 'notify_on_status_change';
            $contacts = \App\Models\EmailContact::where($field, true)->where('is_active', true)->get();
        }

        if ($contacts->isEmpty())
            return;

        $appName = config('app.name', 'Performia');
        $appUrl = rtrim(config('app.url'), '/');
        $user = auth()->user();
        $subject = $event === 'complete'
            ? "✅ Task Completed — {$workLog->title}"
            : "🔄 Task Status Updated — {$workLog->title}";

        $html = $this->buildNotificationHtml($appName, $appUrl, $subject, $workLog, $statusName, $user->name);

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

    private function notifySystemUsers(WorkLog $workLog, array $userIds, string $statusName): void
    {
        $users = \App\Models\User::whereIn('id', $userIds)->get();
        if ($users->isEmpty())
            return;

        $appName = config('app.name', 'Performia');
        $appUrl = rtrim(config('app.url'), '/');
        $sender = auth()->user();
        $subject = "🔔 Task Update: \"{$workLog->title}\" — {$statusName}";

        $html = $this->buildNotificationHtml($appName, $appUrl, $subject, $workLog, $statusName, $sender->name);

        foreach ($users as $user) {
            try {
                \Mail::send([], [], function (\Illuminate\Mail\Message $mail) use ($user, $subject, $html) {
                    $mail->to($user->email, $user->name)->subject($subject)->html($html);
                });
            } catch (\Throwable $e) {
                \Log::error("User notify failed to {$user->email}: " . $e->getMessage());
            }
        }
    }

    private function buildNotificationHtml(string $appName, string $appUrl, string $subject, WorkLog $workLog, string $statusName, string $senderName): string
    {
        $title = e($workLog->title);
        $date = $workLog->log_date->format('d M Y');
        $app = optional($workLog->application)->name;
        $appLine = $app ? "<tr><td style='padding:5px 0;color:#64748b;width:110px;'>Application</td><td style='padding:5px 0;color:#334155;'>" . e($app) . '</td></tr>' : '';

        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;'>
        <div style='max-width:520px;margin:24px auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'>
          <div style='background:#0d9488;padding:16px 22px;'>
            <div style='color:#fff;font-size:16px;font-weight:700;'>{$appName}</div>
            <div style='color:#99f6e4;font-size:11px;margin-top:2px;'>Task Notification</div>
          </div>
          <div style='padding:20px 22px;'>
            <table style='width:100%;border-collapse:collapse;font-size:12px;'>
              <tr><td style='padding:5px 0;color:#64748b;width:110px;'>Task</td><td style='padding:5px 0;font-weight:600;color:#1e293b;'>{$title}</td></tr>
              <tr><td style='padding:5px 0;color:#64748b;'>Status</td><td style='padding:5px 0;'><span style='background:#f0fdfa;color:#0f766e;padding:2px 8px;border-radius:10px;font-weight:600;font-size:11px;'>{$statusName}</span></td></tr>
              <tr><td style='padding:5px 0;color:#64748b;'>Date</td><td style='padding:5px 0;color:#334155;'>{$date}</td></tr>
              {$appLine}
              <tr><td style='padding:5px 0;color:#64748b;'>Updated by</td><td style='padding:5px 0;color:#334155;'>{$senderName}</td></tr>
            </table>
            <div style='margin-top:16px;'>
              <a href='{$appUrl}/work-logs' style='display:inline-block;padding:8px 18px;background:#0d9488;color:#fff;text-decoration:none;border-radius:8px;font-size:12px;font-weight:600;'>View Work Logs →</a>
            </div>
          </div>
          <div style='padding:10px 22px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:10px;color:#94a3b8;'>
            Automated notification from {$appName}
          </div>
        </div></body></html>";
    }

    public function storeFeedback(Request $request, WorkLog $workLog)
    {
        $validated = $request->validate([
            'feedback_type' => 'required|in:self,manager',
            'comment' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $feedback = WorkLogFeedback::create([
            'work_log_id' => $workLog->id,
            'user_id' => auth()->id(),
            'feedback_type' => $validated['feedback_type'],
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
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
            'data' => $feedback->load('user'),
        ]);
    }

    public function forwardReasons(Request $request)
    {
        $reasons = \App\Models\ForwardReason::where('is_active', true)->orderBy('reason')->get(['id', 'reason']);
        return response()->json($reasons);
    }

    public function clone(WorkLog $workLog)
    {
        if ($workLog->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $newLog = $workLog->replicate();
        $newLog->log_date = now()->toDateString();
        $newLog->is_cloned = true;
        $newLog->cloned_from_id = $workLog->id;
        $newLog->score_calculated = 0;
        $newLog->created_at = now();
        $newLog->updated_at = now();
        $newLog->save();

        // Clone links
        foreach ($workLog->links as $link) {
            $newLog->links()->create(['title' => $link->title, 'url' => $link->url]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task cloned successfully. Edit it to update details.',
            'data' => $newLog->load(['subKra.kra', 'application', 'priority', 'status']),
        ]);
    }

    public function forward(Request $request, WorkLog $workLog)
    {
        if ($workLog->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'forward_date' => 'required|date|after:' . $workLog->log_date->toDateString(),
            'reason'       => 'required|string|max:255',
        ]);

        $workLog->update([
            'log_date' => $validated['forward_date'],
            'remark' => ($workLog->remark ? $workLog->remark . ' | ' : '') . 'Forwarded: ' . $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task forwarded to ' . Carbon::parse($validated['forward_date'])->format('d M Y') . '.',
        ]);
    }

    public function downloadAttachment(WorkLogAttachment $attachment)
    {
        if ($attachment->workLog->user_id !== auth()->id())
            abort(403);

        $filePath = storage_path('app/public/' . $attachment->file_path);
        if (!file_exists($filePath))
            abort(404, 'File not found');

        return response()->download($filePath, $attachment->original_name);
    }

    public function deleteAttachment(WorkLogAttachment $attachment)
    {
        if ($attachment->workLog->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'Attachment deleted']);
    }
}
