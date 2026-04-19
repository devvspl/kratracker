<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class KraSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $kras;
    protected $year;
    protected $month;

    public function __construct($kras, $year, $month)
    {
        $this->kras  = $kras;
        $this->year  = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->kras as $kra) {
            foreach ($kra->subKras as $subKra) {
                $logs   = $subKra->workLogs;
                $count  = $logs->count();
                $logic  = optional($subKra->logic);

                // Per-log score breakdown aggregates
                $totalAchievement = $logs->sum('achievement_value');
                $totalTarget      = $logs->sum('target_value_snapshot');

                // Achievement % based on logic type
                if ($logic->scoring_type === 'proportional') {
                    $achievementPct = $totalTarget > 0
                        ? round(min(($totalAchievement / $totalTarget) * 100, 100), 2)
                        : 0;
                } else {
                    // binary: % of tasks where achievement >= target
                    $binaryPassed   = $logs->filter(fn($l) => $l->achievement_value >= $l->target_value_snapshot)->count();
                    $achievementPct = $count > 0 ? round(($binaryPassed / $count) * 100, 2) : 0;
                }

                // Avg base score (before multipliers) — recalculate
                $avgBaseScore = $logs->avg(function ($log) use ($logic) {
                    $ach = (float) $log->achievement_value;
                    $tgt = (float) $log->target_value_snapshot;
                    if ($logic->scoring_type === 'proportional') {
                        return $tgt > 0 ? min(($ach / $tgt) * 100, 100) : 0;
                    }
                    return $ach >= $tgt ? 100 : 0;
                }) ?? 0;

                // Avg status multiplier
                $avgStatusMult = $logs->avg(function ($log) {
                    $s = optional($log->status)->name ?? '';
                    return match(true) {
                        str_contains($s, 'Completed')   => 1.0,
                        str_contains($s, 'In Progress') => 0.7,
                        str_contains($s, 'On Hold')     => 0.4,
                        default                         => 0.0,
                    };
                }) ?? 0;

                // Avg priority bonus
                $avgPriorityBonus = $logs->avg(function ($log) {
                    $s = optional($log->status)->name ?? '';
                    $mult = str_contains($s, 'Completed') || str_contains($s, 'In Progress') || str_contains($s, 'On Hold');
                    if (!$mult) return 0;
                    return match((int)(optional($log->priority)->level ?? 0)) {
                        3 => 10, 2 => 5, default => 0
                    };
                }) ?? 0;

                // Avg test bonus
                $avgTestBonus = $logs->avg(function ($log) {
                    $s = optional($log->status)->name ?? '';
                    $mult = str_contains($s, 'Completed') || str_contains($s, 'In Progress') || str_contains($s, 'On Hold');
                    if (!$mult) return 0;
                    return match($log->test_status) {
                        'Passed' => 5, 'Failed' => -10, default => 0
                    };
                }) ?? 0;

                // Avg duration bonus
                $avgDurBonus = $logs->avg(function ($log) {
                    $s = optional($log->status)->name ?? '';
                    $mult = str_contains($s, 'Completed') || str_contains($s, 'In Progress') || str_contains($s, 'On Hold');
                    if (!$mult) return 0;
                    $t = (float)($log->total_duration ?? 0);
                    $a = (float)($log->actual_duration ?? 0);
                    if ($t > 0 && $a > 0) {
                        if ($a <= $t)          return 5;
                        if ($a > $t * 1.2)     return -5;
                    }
                    return 0;
                }) ?? 0;

                // Avg feedback bonus
                $avgFeedbackBonus = $logs->avg(function ($log) {
                    $s = optional($log->status)->name ?? '';
                    $mult = str_contains($s, 'Completed') || str_contains($s, 'In Progress') || str_contains($s, 'On Hold');
                    if (!$mult) return 0;
                    $fbs = $log->feedbacks;
                    if ($fbs->isEmpty()) return 0;
                    $avg = $fbs->avg('rating');
                    return match(true) {
                        $avg >= 4.5 => 10, $avg >= 3.5 => 5, $avg >= 2.5 => 0, default => -5
                    };
                }) ?? 0;

                $avgFinalScore  = round($logs->avg('score_calculated') ?? 0, 2);
                $weightedScore  = round(($avgFinalScore * $subKra->weightage) / 100, 2);
                $completed      = $logs->filter(fn($l) => str_contains(optional($l->status)->name ?? '', 'Completed'))->count();
                $totalHours     = round($logs->sum('actual_duration'), 2);

                $data->push(compact(
                    'kra', 'subKra', 'logic', 'count', 'completed',
                    'totalAchievement', 'totalTarget', 'achievementPct',
                    'avgBaseScore', 'avgStatusMult', 'avgPriorityBonus',
                    'avgTestBonus', 'avgDurBonus', 'avgFeedbackBonus',
                    'avgFinalScore', 'weightedScore', 'totalHours'
                ));
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'KRA', 'KRA Weight (%)',
            'Sub-KRA', 'Sub-KRA Weight (%)', 'Logic', 'Review Period',
            'Logs', 'Completed',
            'Total Achievement', 'Total Target', 'Achievement %',
            'Avg Base Score (%)',
            'Avg Status Mult', 'Avg Priority Bonus',
            'Avg Test Bonus', 'Avg Duration Bonus', 'Avg Feedback Bonus',
            'Avg Final Score (%)', 'Weighted Score',
            'Total Hours',
            'Period',
        ];
    }

    public function map($row): array
    {
        return [
            $row['kra']->name,
            $row['kra']->total_weightage . '%',
            $row['subKra']->name,
            $row['subKra']->weightage . '%',
            optional($row['logic'])->scoring_type ?? '—',
            $row['subKra']->review_period,
            $row['count'],
            $row['completed'],
            number_format($row['totalAchievement'], 2),
            number_format($row['totalTarget'], 2),
            number_format($row['achievementPct'], 2) . '%',
            number_format($row['avgBaseScore'], 2) . '%',
            round($row['avgStatusMult'], 2) . '×',
            ($row['avgPriorityBonus'] >= 0 ? '+' : '') . round($row['avgPriorityBonus'], 1),
            ($row['avgTestBonus'] >= 0 ? '+' : '') . round($row['avgTestBonus'], 1),
            ($row['avgDurBonus'] >= 0 ? '+' : '') . round($row['avgDurBonus'], 1),
            ($row['avgFeedbackBonus'] >= 0 ? '+' : '') . round($row['avgFeedbackBonus'], 1),
            number_format($row['avgFinalScore'], 2) . '%',
            number_format($row['weightedScore'], 2),
            number_format($row['totalHours'], 2) . 'h',
            $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28, 'B' => 14, 'C' => 28, 'D' => 16, 'E' => 16, 'F' => 14,
            'G' => 8,  'H' => 10,
            'I' => 16, 'J' => 14, 'K' => 14,
            'L' => 16,
            'M' => 14, 'N' => 16, 'O' => 14, 'P' => 16, 'Q' => 16,
            'R' => 16, 'S' => 14,
            'T' => 12, 'U' => 12,
        ];
    }
}
