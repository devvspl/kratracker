<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class WorkLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            'Date',
            'Title',
            'Description',
            'KRA',
            'Sub-KRA',
            'Application',
            'Module',
            'Start Time',
            'End Time',
            'Total Duration (h)',
            'Actual Duration (h)',
            'Priority',
            'Status',
            'Test Status',
            'Achievement',
            'Target',
            'Achievement %',
            'Base Score (%)',
            'Status Multiplier',
            'Priority Bonus',
            'Test Bonus',
            'Duration Bonus',
            'Feedback Bonus',
            'Final Score (%)',
            'Sub-KRA Weight (%)',
            'Weighted Score',
            'Remark',
        ];
    }

    public function map($log): array
    {
        $subKra = $log->subKra;
        $logic  = optional($subKra->logic);

        $achievement = (float) $log->achievement_value;
        $target      = (float) $log->target_value_snapshot;

        if ($logic->scoring_type === 'proportional') {
            $baseScore = $target > 0 ? min(($achievement / $target) * 100, 100) : 0;
        } else {
            $baseScore = $achievement >= $target ? 100 : 0;
        }
        $achievementPct = $target > 0 ? round(($achievement / $target) * 100, 2) : 0;

        $statusName = optional($log->status)->name ?? '—';
        $statusMult = match(true) {
            str_contains($statusName, 'Completed')   => 1.0,
            str_contains($statusName, 'In Progress') => 0.7,
            str_contains($statusName, 'On Hold')     => 0.4,
            default                                  => 0.0,
        };

        $priorityLevel = (int)(optional($log->priority)->level ?? 0);
        $priorityBonus = $statusMult > 0 ? match($priorityLevel) { 3=>10, 2=>5, default=>0 } : 0;

        $testStatus = $log->test_status ?? '—';
        $testBonus  = $statusMult > 0 ? match($testStatus) { 'Passed'=>5, 'Failed'=>-10, default=>0 } : 0;

        $totalDur  = (float)($log->total_duration  ?? 0);
        $actualDur = (float)($log->actual_duration ?? 0);
        $durBonus  = 0;
        if ($statusMult > 0 && $totalDur > 0 && $actualDur > 0) {
            if ($actualDur <= $totalDur)          $durBonus = 5;
            elseif ($actualDur > $totalDur * 1.2) $durBonus = -5;
        }

        $feedbacks     = $log->feedbacks;
        $avgFeedback   = $feedbacks->isNotEmpty() ? round($feedbacks->avg('rating'), 1) : null;
        $feedbackBonus = 0;
        if ($statusMult > 0 && $avgFeedback !== null) {
            $feedbackBonus = match(true) {
                $avgFeedback >= 4.5 => 10,
                $avgFeedback >= 3.5 => 5,
                $avgFeedback >= 2.5 => 0,
                default             => -5,
            };
        }

        $finalScore    = round(max(0, min(100, ($baseScore * $statusMult) + $priorityBonus + $testBonus + $durBonus + $feedbackBonus)), 2);
        $weightage     = (float)optional($subKra)->weightage;
        $weightedScore = round(($finalScore * $weightage) / 100, 2);

        $startTime = $log->start_time ? \Carbon\Carbon::parse($log->start_time)->format('H:i') : '—';
        $endTime   = $log->end_time   ? \Carbon\Carbon::parse($log->end_time)->format('H:i')   : '—';

        return [
            $log->log_date->format('Y-m-d'),
            $log->title,
            $log->description ?? '',
            optional($subKra->kra)->name ?? '—',
            optional($subKra)->name ?? '—',
            optional($log->application)->name ?? '—',
            optional($log->module)->name ?? '—',
            $startTime,
            $endTime,
            $totalDur,
            $actualDur,
            optional($log->priority)->name ?? '—',
            $statusName,
            $testStatus,
            $achievement,
            $target,
            $achievementPct . '%',
            round($baseScore, 2) . '%',
            $statusMult . '×',
            ($priorityBonus >= 0 ? '+' : '') . $priorityBonus,
            ($testBonus >= 0 ? '+' : '') . $testBonus,
            ($durBonus >= 0 ? '+' : '') . $durBonus,
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
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13, 'B' => 32, 'C' => 35, 'D' => 26, 'E' => 26,
            'F' => 18, 'G' => 18, 'H' => 11, 'I' => 11,
            'J' => 14, 'K' => 14, 'L' => 14, 'M' => 16, 'N' => 14,
            'O' => 12, 'P' => 12, 'Q' => 14, 'R' => 14, 'S' => 14,
            'T' => 14, 'U' => 12, 'V' => 14, 'W' => 14,
            'X' => 14, 'Y' => 16, 'Z' => 14, 'AA' => 28,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $lastRow    = $sheet->getHighestRow();
                $lastCol    = $sheet->getHighestColumn();
                $range      = 'A1:' . $lastCol . $lastRow;

                // Thin border on all cells
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // Slightly thicker outer border
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['rgb' => '94A3B8'],
                        ],
                    ],
                ]);

                // Alternate row shading for data rows
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }
                }

                // Wrap text and vertical align top for all data rows
                $sheet->getStyle('A2:' . $lastCol . $lastRow)->applyFromArray([
                    'alignment' => [
                        'vertical'  => Alignment::VERTICAL_TOP,
                        'wrapText'  => true,
                    ],
                ]);
            },
        ];
    }
}
