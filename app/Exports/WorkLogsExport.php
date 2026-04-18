<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkLogsExport implements FromCollection, WithHeadings, WithMapping
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
            'KRA',
            'Sub-KRA',
            'Title',
            'Description',
            'Application',
            'Achievement',
            'Target',
            'Score (%)',
            'Weighted Score',
            'Status',
            'Priority',
            'Time Spent (hrs)',
            'Feedback Count',
        ];
    }

    public function map($workLog): array
    {
        return [
            $workLog->log_date->format('Y-m-d'),
            $workLog->subKra->kra->name,
            $workLog->subKra->name,
            $workLog->title,
            $workLog->description,
            $workLog->application ? $workLog->application->name : 'N/A',
            $workLog->achievement_value,
            $workLog->target_value_snapshot,
            number_format($workLog->score_calculated, 2),
            number_format($workLog->weighted_score, 2),
            $workLog->status->name,
            $workLog->priority->name,
            $workLog->time_spent_hours,
            $workLog->feedbacks->count(),
        ];
    }
}
