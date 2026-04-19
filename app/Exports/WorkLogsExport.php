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

class WorkLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $workLogs;

    public function __construct($workLogs)
    {
        $this->workLogs = $workLogs;
    }

    public function collection()
    {
        return $this->workLogs;
    }

    public function headings(): array
    {
        return [
            // Identity
            'Date', 'Title', 'KRA', 'Sub-KRA', 'Application', 'Module',
            // Achievement vs Target
            'Achievement', 'Target', 'Achievement %',
            // Logic
            'Logic Type',
            // Base score (before multipliers)
            'Base Score (%)',
            // Multipliers & bonuses
            'Status', 'Status Multiplier',
            'Priority', 'Priority Bonus',
            'Test Status', 'Test Bonus',
            'Total Dur (h)', 'Actual Dur (h)', 'Duration Bonus',
            'Avg Feedback', 'Feedback Bonus',
            // Final
            'Final Score (%)',
            'Sub-KRA Weight (%)', 'Weighted Score',
            // Meta
            'Remark',
        ];
    }

    public function map($log): array
    {
        $subKra = $log->subKra;
        $logic  = optional($subKra->logic);

        // ── Base score ────────────────────────────────────────────────────────
        $achievement = (float) $log->achievement_value;
        $target      = (float) $log->target_value_snapshot;

        if ($logic->scoring_type === 'proportional') {
            $baseScore = $target > 0 ? min(($achievement / $target) * 100, 100) : 0;
        } else {
            // binary
            $baseScore = $achievement >= $target ? 100 : 0;
        }
        $achievementPct = $target > 0 ? round(($achievement / $target) * 100, 2) : 0;

        // ── Status multiplier ─────────────────────────────────────────────────
        $statusName = optional($log->status)->name ?? '—';
        $statusMult = match(true) {
            str_contains($statusName, 'Completed')   => 1.0,
            str_contains($statusName, 'In Progress') => 0.7,
            str_contains($statusName, 'On Hold')     => 0.4,
            default                                  => 0.0,
        };

        // ── Priority bonus ────────────────────────────────────────────────────
        $priorityName  = optional($log->priority)->name ?? '—';
        $priorityLevel = (int) (optional($log->priority)->level ?? 0);
        $priorityBonus = $statusMult > 0 ? match($priorityLevel) {
            3 => 10, 2 => 5, default => 0
        } : 0;

        // ── Test bonus ────────────────────────────────────────────────────────
        $testStatus = $log->test_status ?? '—';
        $testBonus  = $statusMult > 0 ? match($testStatus) {
            'Passed' => 5, 'Failed' => -10, default => 0
        } : 0;

        // ── Duration bonus ────────────────────────────────────────────────────
        $totalDur  = (float) ($log->total_duration  ?? 0);
        $actualDur = (float) ($log->actual_duration ?? 0);
        $durBonus  = 0;
        if ($statusMult > 0 && $totalDur > 0 && $actualDur > 0) {
            if ($actualDur <= $totalDur)          $durBonus = 5;
            elseif ($actualDur > $totalDur * 1.2) $durBonus = -5;
        }

        // ── Feedback bonus ────────────────────────────────────────────────────
        $feedbacks    = $log->feedbacks;
        $avgFeedback  = $feedbacks->isNotEmpty() ? round($feedbacks->avg('rating'), 1) : null;
        $feedbackBonus = 0;
        if ($statusMult > 0 && $avgFeedback !== null) {
            $feedbackBonus = match(true) {
                $avgFeedback >= 4.5 => 10,
                $avgFeedback >= 3.5 => 5,
                $avgFeedback >= 2.5 => 0,
                default             => -5,
            };
        }

        // ── Final score ───────────────────────────────────────────────────────
        $finalScore   = round(max(0, min(100, ($baseScore * $statusMult) + $priorityBonus + $testBonus + $durBonus + $feedbackBonus)), 2);
        $weightage    = (float) optional($subKra)->weightage;
        $weightedScore = round(($finalScore * $weightage) / 100, 2);

        return [
            $log->log_date->format('Y-m-d'),
            $log->title,
            optional($subKra->kra)->name ?? '—',
            optional($subKra)->name ?? '—',
            optional($log->application)->name ?? '—',
            optional($log->module)->name ?? '—',
            $achievement,
            $target,
            $achievementPct . '%',
            $logic->scoring_type ?? '—',
            round($baseScore, 2) . '%',
            $statusName,
            $statusMult . '×',
            $priorityName,
            ($priorityBonus >= 0 ? '+' : '') . $priorityBonus,
            $testStatus,
            ($testBonus >= 0 ? '+' : '') . $testBonus,
            $totalDur,
            $actualDur,
            ($durBonus >= 0 ? '+' : '') . $durBonus,
            $avgFeedback ?? '—',
            ($feedbackBonus >= 0 ? '+' : '') . $feedbackBonus,
            $finalScore . '%',
            $weightage . '%',
            $weightedScore,
            $log->remark ?? '',
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
            'A' => 13, 'B' => 32, 'C' => 26, 'D' => 26, 'E' => 18, 'F' => 18,
            'G' => 12, 'H' => 12, 'I' => 14,
            'J' => 16, 'K' => 14,
            'L' => 16, 'M' => 16,
            'N' => 14, 'O' => 14,
            'P' => 14, 'Q' => 12,
            'R' => 13, 'S' => 13, 'T' => 14,
            'U' => 13, 'V' => 14,
            'W' => 14, 'X' => 16, 'Y' => 14,
            'Z' => 28,
        ];
    }
}
