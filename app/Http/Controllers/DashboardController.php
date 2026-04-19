<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\Kra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Date range — default to current month
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        $currentMonth = $dateFrom->month;
        $currentYear  = $dateFrom->year;

        // Summary counts
        $baseQuery = WorkLog::where('user_id', $user->id)
            ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        $tasksLogged    = (clone $baseQuery)->count();
        $tasksCompleted = (clone $baseQuery)->whereHas('status', fn($q) => $q->where('name', 'Completed'))->count();
        $pendingTasks   = (clone $baseQuery)->whereHas('status', fn($q) => $q->whereIn('name', ['Not Started', 'In Progress']))->count();
        $totalHours     = (clone $baseQuery)->sum('actual_duration');

        // Overall KRA score
        $overallScore = $this->calculateOverallScore($user->id, $dateFrom, $dateTo);

        // Score factor breakdown (for display)
        $scoreFactors = $this->getScoreFactors($user->id, $dateFrom, $dateTo);

        // Sub-KRA-wise scores
        $kraScores = $this->getSubKraWiseScores($user->id, $dateFrom, $dateTo);

        // Daily trend within range
        $dailyTrend = WorkLog::where('user_id', $user->id)
            ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent work logs (latest 8)
        $recentLogs = WorkLog::with(['subKra.kra', 'status', 'application'])
            ->where('user_id', $user->id)
            ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->latest('log_date')
            ->limit(8)
            ->get();

        // Status breakdown
        $statusBreakdown = WorkLog::where('user_id', $user->id)
            ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->with('status')
            ->get()
            ->groupBy('status.name')
            ->map(fn($g) => $g->count());

        // KRA matrix with sub-KRAs, logic, targets and work logs
        $kraMatrix = $this->buildKraMatrix($user->id, $dateFrom, $dateTo);

        return view('dashboard', compact(
            'overallScore', 'tasksLogged', 'tasksCompleted',
            'pendingTasks', 'totalHours', 'kraScores', 'dailyTrend',
            'recentLogs', 'statusBreakdown', 'dateFrom', 'dateTo',
            'scoreFactors', 'kraMatrix'
        ));
    }

    private function buildKraMatrix($userId, $dateFrom, $dateTo): \Illuminate\Support\Collection
    {
        return Kra::with([
            'subKras.logic',
            'subKras.periodTargets',
            'subKras.workLogs' => function ($q) use ($userId, $dateFrom, $dateTo) {
                $q->where('user_id', $userId)
                  ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                  ->with(['status', 'priority', 'application', 'module', 'feedbacks'])
                  ->orderByDesc('log_date');
            },
        ])->where('is_active', true)->orderBy('id')->get()->map(function ($kra) use ($dateFrom) {
            $subKras = $kra->subKras->map(function ($sub) use ($dateFrom) {
                $logs      = $sub->workLogs;
                $count     = $logs->count();
                $avgScore  = $count > 0 ? round($logs->avg('score_calculated'), 1) : 0;
                $completed = $logs->filter(fn($l) => str_contains(optional($l->status)->name ?? '', 'Completed'))->count();
                $totalHrs  = $logs->sum('actual_duration');

                // Period target for current period
                $target = $sub->periodTargets
                    ->where('period_year', $dateFrom->year)
                    ->first();

                return [
                    'id'           => $sub->id,
                    'name'         => $sub->name,
                    'weightage'    => $sub->weightage,
                    'unit'         => $sub->unit,
                    'measure_type' => $sub->measure_type,
                    'review_period'=> $sub->review_period,
                    'logic_name'   => $sub->logic->name ?? '—',
                    'logic_type'   => $sub->logic->scoring_type ?? '—',
                    'target_value' => $target?->target_value ?? '—',
                    'logs_count'   => $count,
                    'avg_score'    => $avgScore,
                    'completed'    => $completed,
                    'total_hours'  => $totalHrs,
                    'logs'         => $logs,
                ];
            });

            $kraAvgScore = $subKras->where('logs_count', '>', 0)->avg('avg_score') ?? 0;

            return [
                'id'          => $kra->id,
                'name'        => $kra->name,
                'weightage'   => $kra->total_weightage,
                'description' => $kra->description,
                'avg_score'   => round($kraAvgScore, 1),
                'sub_kras'    => $subKras,
            ];
        });
    }
    

    private function getScoreFactors($userId, $dateFrom, $dateTo): array
    {
        $logs = WorkLog::where('user_id', $userId)
            ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->with(['status', 'priority', 'feedbacks'])
            ->get();

        $total = $logs->count();
        if ($total === 0) {
            return [
                'completion_rate'   => 0,
                'high_priority_pct' => 0,
                'test_pass_rate'    => 0,
                'duration_eff_pct'  => 0,
                'avg_feedback'      => 0,
                'avg_base_score'    => 0,
                'avg_final_score'   => 0,
            ];
        }

        $completed      = $logs->filter(fn($l) => str_contains(optional($l->status)->name ?? '', 'Completed'))->count();
        $highPriority   = $logs->filter(fn($l) => (optional($l->priority)->level ?? 0) >= 3)->count();

        $testable       = $logs->filter(fn($l) => in_array($l->test_status, ['Passed', 'Failed']));
        $testPassed     = $testable->filter(fn($l) => $l->test_status === 'Passed')->count();

        $durLogs        = $logs->filter(fn($l) => ($l->total_duration ?? 0) > 0 && ($l->actual_duration ?? 0) > 0);
        $onTime         = $durLogs->filter(fn($l) => $l->actual_duration <= $l->total_duration)->count();

        $allFeedbacks   = $logs->flatMap(fn($l) => $l->feedbacks);
        $avgFeedback    = $allFeedbacks->isNotEmpty() ? round($allFeedbacks->avg('rating'), 1) : 0;

        return [
            'completion_rate'   => $total > 0 ? round(($completed / $total) * 100) : 0,
            'high_priority_pct' => $total > 0 ? round(($highPriority / $total) * 100) : 0,
            'test_pass_rate'    => $testable->count() > 0 ? round(($testPassed / $testable->count()) * 100) : null,
            'duration_eff_pct'  => $durLogs->count() > 0 ? round(($onTime / $durLogs->count()) * 100) : null,
            'avg_feedback'      => $avgFeedback,
            'avg_final_score'   => round($logs->avg('score_calculated'), 1),
        ];
    }

    private function calculateOverallScore($userId, $dateFrom, $dateTo)
    {
        $workLogs = WorkLog::where('user_id', $userId)
            ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get();

        if ($workLogs->isEmpty()) return 0;

        // Simple average of all task scores (already factor-adjusted 0-100)
        return round($workLogs->avg('score_calculated'), 1);
    }

    private function getSubKraWiseScores($userId, $dateFrom, $dateTo)
    {
        return \App\Models\SubKra::with(['kra', 'workLogs' => function ($q) use ($userId, $dateFrom, $dateTo) {
            $q->where('user_id', $userId)
              ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        }])->get()
        ->filter(fn($subKra) => $subKra->workLogs->isNotEmpty())
        ->map(function ($subKra) {
            $logs  = $subKra->workLogs;
            $count = $logs->count();
            return [
                'name'      => $subKra->kra->name . ' › ' . $subKra->name,
                'score'     => $count > 0 ? round($logs->sum('score_calculated') / $count, 2) : 0,
                'weightage' => $subKra->weightage,
                'count'     => $count,
            ];
        })->values();
    }

    private function getKraWiseScores($userId, $dateFrom, $dateTo)
    {
        return Kra::with(['subKras.workLogs' => function ($q) use ($userId, $dateFrom, $dateTo) {
            $q->where('user_id', $userId)
              ->whereBetween('log_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        }])->get()->map(function ($kra) {
            $totalScore = 0;
            $count      = 0;
            foreach ($kra->subKras as $subKra) {
                foreach ($subKra->workLogs as $log) {
                    $totalScore += $log->score_calculated;
                    $count++;
                }
            }
            return [
                'name'      => $kra->name,
                'score'     => $count > 0 ? round($totalScore / $count, 2) : 0,
                'weightage' => $kra->total_weightage,
            ];
        });
    }
}
