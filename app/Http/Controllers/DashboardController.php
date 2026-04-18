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
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Overall KRA score (weighted average)
        $overallScore = $this->calculateOverallScore($user->id, $currentYear, $currentMonth);

        // Tasks logged this month
        $tasksLoggedThisMonth = WorkLog::where('user_id', $user->id)
            ->whereMonth('log_date', $currentMonth)
            ->whereYear('log_date', $currentYear)
            ->count();

        // Tasks completed this month
        $tasksCompletedThisMonth = WorkLog::where('user_id', $user->id)
            ->whereMonth('log_date', $currentMonth)
            ->whereYear('log_date', $currentYear)
            ->whereHas('status', function($q) {
                $q->where('name', 'Completed');
            })
            ->count();

        // Pending/overdue tasks
        $pendingTasks = WorkLog::where('user_id', $user->id)
            ->whereHas('status', function($q) {
                $q->whereIn('name', ['Not Started', 'In Progress']);
            })
            ->count();

        // KRA-wise scores
        $kraScores = $this->getKraWiseScores($user->id, $currentYear, $currentMonth);

        // Daily work log trend (last 30 days)
        $dailyTrend = $this->getDailyTrend($user->id);

        return view('dashboard', compact(
            'overallScore',
            'tasksLoggedThisMonth',
            'tasksCompletedThisMonth',
            'pendingTasks',
            'kraScores',
            'dailyTrend'
        ));
    }

    private function calculateOverallScore($userId, $year, $month)
    {
        $workLogs = WorkLog::where('user_id', $userId)
            ->whereYear('log_date', $year)
            ->whereMonth('log_date', $month)
            ->with('subKra')
            ->get();

        if ($workLogs->isEmpty()) {
            return 0;
        }

        $totalWeightedScore = 0;
        $totalWeightage = 0;

        foreach ($workLogs as $log) {
            $weightedScore = ($log->score_calculated * $log->subKra->weightage) / 100;
            $totalWeightedScore += $weightedScore;
            $totalWeightage += $log->subKra->weightage;
        }

        return $totalWeightage > 0 ? round($totalWeightedScore, 2) : 0;
    }

    private function getKraWiseScores($userId, $year, $month)
    {
        return Kra::with(['subKras.workLogs' => function($q) use ($userId, $year, $month) {
            $q->where('user_id', $userId)
              ->whereYear('log_date', $year)
              ->whereMonth('log_date', $month);
        }])->get()->map(function($kra) {
            $totalScore = 0;
            $count = 0;
            
            foreach ($kra->subKras as $subKra) {
                foreach ($subKra->workLogs as $log) {
                    $totalScore += $log->score_calculated;
                    $count++;
                }
            }
            
            return [
                'name' => $kra->name,
                'score' => $count > 0 ? round($totalScore / $count, 2) : 0,
                'weightage' => $kra->total_weightage
            ];
        });
    }

    private function getDailyTrend($userId)
    {
        $last30Days = Carbon::now()->subDays(30);
        
        return WorkLog::where('user_id', $userId)
            ->where('log_date', '>=', $last30Days)
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
