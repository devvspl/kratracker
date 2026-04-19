<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Overdue + pending + daily reminder — every hour on weekdays
Schedule::command('kra:check-overdue')->hourly()->weekdays();

// Recalculate all scores nightly
Schedule::command('kra:recalculate-scores')->dailyAt('00:30');

// Daily report — weekdays at 08:00
Schedule::command('kra:send-reports daily')->weekdays()->dailyAt('08:00');

// Weekly report — every Monday at 08:30
Schedule::command('kra:send-reports weekly')->weekly()->mondays()->at('08:30');

// Monthly report — 1st of every month at 09:00
Schedule::command('kra:send-reports monthly')->monthlyOn(1, '09:00');
