<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkLog;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueTasks extends Command
{
    protected $signature   = 'kra:check-overdue';
    protected $description = 'Send overdue, pending, and daily reminder notifications';

    public function handle(NotificationService $notifier): int
    {
        $this->checkOverdue($notifier);
        $this->checkPendingReview($notifier);
        $this->sendDailyReminder($notifier);

        $this->info('Notification check complete.');
        return 0;
    }

    /** Tasks still In Progress / Not Started older than 3 days */
    private function checkOverdue(NotificationService $notifier): void
    {
        $tasks = WorkLog::whereHas('status', fn($q) =>
                $q->whereIn('name', ['Not Started', 'In Progress'])
            )
            ->where('log_date', '<', Carbon::now()->subDays(3))
            ->with('user')
            ->get();

        foreach ($tasks as $task) {
            $notifier->notify(
                $task->user,
                'task_overdue',
                "Task \"{$task->title}\" has been {$task->status->name} since {$task->log_date->diffForHumans()} and needs attention.",
                ['work_log_id' => $task->id, 'title' => $task->title, 'log_date' => $task->log_date->toDateString()]
            );
        }

        $this->info("Overdue check: {$tasks->count()} tasks.");
    }

    /** Tasks logged today with no status update (still Not Started) */
    private function checkPendingReview(NotificationService $notifier): void
    {
        $tasks = WorkLog::whereHas('status', fn($q) => $q->where('name', 'Not Started'))
            ->whereDate('log_date', Carbon::today())
            ->with('user')
            ->get();

        foreach ($tasks as $task) {
            $notifier->notify(
                $task->user,
                'pending_review',
                "Task \"{$task->title}\" logged today is still Not Started. Don't forget to update its status.",
                ['work_log_id' => $task->id, 'title' => $task->title]
            );
        }

        $this->info("Pending review check: {$tasks->count()} tasks.");
    }

    /** Daily reminder: users who haven't logged anything today */
    private function sendDailyReminder(NotificationService $notifier): void
    {
        // Only send on weekdays
        if (Carbon::now()->isWeekend()) return;

        $usersWithLogsToday = WorkLog::whereDate('log_date', Carbon::today())
            ->pluck('user_id')
            ->unique();

        $usersWithoutLogs = User::whereNotIn('id', $usersWithLogsToday)->get();

        foreach ($usersWithoutLogs as $user) {
            $notifier->notify(
                $user,
                'daily_reminder',
                "You haven't logged any work today (" . Carbon::today()->format('d M Y') . "). Keep your KRA tracker up to date!",
                ['date' => Carbon::today()->toDateString()]
            );
        }

        $this->info("Daily reminder: sent to {$usersWithoutLogs->count()} users.");
    }
}
