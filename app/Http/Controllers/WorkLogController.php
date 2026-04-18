<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\WorkLogFeedback;
use App\Models\SubKra;
use App\Models\Application;
use App\Models\Priority;
use App\Models\TaskStatus;
use App\Models\PeriodTarget;
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

        $workLogs    = $query->latest('log_date')->paginate(20);
        $subKras     = SubKra::with('kra')->where('is_active', true)->get();
        $applications= Application::where('is_active', true)->get();
        $priorities  = Priority::where('is_active', true)->orderBy('level', 'desc')->get();
        $statuses    = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();

        return view('work-logs.index', compact('workLogs', 'subKras', 'applications', 'priorities', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_kra_id'       => 'required|exists:sub_kras,id',
            'application_id'   => 'nullable|exists:applications,id',
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
        ]);

        if (isset($validated['log_date'])) {
            $validated['log_date'] = Carbon::parse($validated['log_date'])->format('Y-m-d');
        }

        $totalDur = $validated['total_duration'] ?? $workLog->total_duration ?? 0;
        $actualDur = $validated['actual_duration'] ?? $workLog->actual_duration ?? 0;
        $validated['duration_difference'] = $totalDur - $actualDur;

        $workLog->update($validated);
        $workLog->calculateScore();

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

        return response()->json([
            'success' => true,
            'message' => 'Feedback added successfully',
            'data'    => $feedback->load('user'),
        ]);
    }
}
