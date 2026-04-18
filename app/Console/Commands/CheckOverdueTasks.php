<?php

namespace App\Console\Commands;

use App\Models\WorkLog;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckOverdueTasks extends Command
{
    protected $signature = 'kra:check-overdue';
    protected $description = 'Check for overdue tasks and send notifications';

    public function handle()
    {
        $overdueTasks = WorkLog::whereHas('status', function($q) {
            $q->whereIn('name', ['Not Started', 'In Progress']);
        })
        ->where('log_date', '<', Carbon::now()->subDays(7))
        ->with('user')
        ->get();

        foreach ($overdueTasks as $task) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_overdue',
                'message' => "Task '{$task->title}' is overdue since {$task->log_date->diffForHumans()}",
                'data' => [
                    'work_log_id' => $task->id,
                    'title' => $task->title,
                    'log_date' => $task->log_date->format('Y-m-d'),
                ],
            ]);
        }

        $this->info("Checked {$overdueTasks->count()} overdue tasks.");
        return 0;
    }
}
