<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KraSummaryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $kras;
    protected $year;
    protected $month;

    public function __construct($kras, $year, $month)
    {
        $this->kras = $kras;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $data = collect();
        
        foreach ($this->kras as $kra) {
            foreach ($kra->subKras as $subKra) {
                $workLogs = $subKra->workLogs;
                $totalAchievement = $workLogs->sum('achievement_value');
                $totalTarget = $workLogs->sum('target_value_snapshot');
                $avgScore = $workLogs->avg('score_calculated');
                $weightedScore = ($avgScore * $subKra->weightage) / 100;

                $data->push([
                    'kra' => $kra->name,
                    'sub_kra' => $subKra->name,
                    'weightage' => $subKra->weightage,
                    'target' => $totalTarget,
                    'achievement' => $totalAchievement,
                    'score' => $avgScore,
                    'weighted_score' => $weightedScore,
                    'period' => $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT),
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'KRA',
            'Sub-KRA',
            'Weightage (%)',
            'Target',
            'Achievement',
            'Score (%)',
            'Weighted Score',
            'Period',
        ];
    }

    public function map($row): array
    {
        return [
            $row['kra'],
            $row['sub_kra'],
            $row['weightage'],
            number_format($row['target'], 2),
            number_format($row['achievement'], 2),
            number_format($row['score'], 2),
            number_format($row['weighted_score'], 2),
            $row['period'],
        ];
    }
}
