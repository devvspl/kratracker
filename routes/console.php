<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Overdue + pending + daily reminder — runs every hour on weekdays
Schedule::command('kra:check-overdue')->hourly()->weekdays();

// Recalculate all scores nightly
Schedule::command('kra:recalculate-scores')->dailyAt('00:30');
