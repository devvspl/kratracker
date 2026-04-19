<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\Kra;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkLogsExport;
use App\Exports\KraSummaryExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function exportWorkLogs(Request $request)
    {
        $query = WorkLog::with(['subKra.kra', 'application', 'module', 'priority', 'status', 'feedbacks', 'user'])
            ->where('user_id', auth()->id());

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('log_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('log_date', '<=', $request->date_to);
        }

        $workLogs = $query->get();

        return Excel::download(
            new WorkLogsExport($workLogs),
            'work-logs-' . Carbon::now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportKraSummary(Request $request)
    {
        $userId = auth()->id();
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        $kras = Kra::with(['subKras.logic', 'subKras.workLogs' => function($q) use ($userId, $year, $month) {
            $q->where('user_id', $userId)
              ->whereYear('log_date', $year)
              ->whereMonth('log_date', $month)
              ->with(['status', 'priority', 'feedbacks']);
        }])->get();

        return Excel::download(
            new KraSummaryExport($kras, $year, $month),
            'kra-summary-' . Carbon::now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportAnalyticsPdf(Request $request)
    {
        $user = auth()->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $data = [
            'user' => $user,
            'date' => Carbon::now()->format('F d, Y'),
            'workLogs' => WorkLog::where('user_id', $user->id)
                ->whereMonth('log_date', $currentMonth)
                ->whereYear('log_date', $currentYear)
                ->with(['subKra.kra', 'status', 'priority'])
                ->get(),
        ];

        $pdf = Pdf::loadView('exports.analytics-pdf', $data);
        return $pdf->download('analytics-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}
