<?php

namespace App\Console\Commands;

use App\Models\WorkLog;
use Illuminate\Console\Command;

class RecalculateScores extends Command
{
    protected $signature = 'kra:recalculate-scores';
    protected $description = 'Recalculate scores for all work logs';

    public function handle()
    {
        $this->info('Recalculating scores for all work logs...');
        
        $workLogs = WorkLog::with('subKra.logic')->get();
        $bar = $this->output->createProgressBar($workLogs->count());
        $bar->start();

        foreach ($workLogs as $workLog) {
            $workLog->calculateScore();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully recalculated scores for {$workLogs->count()} work logs.");
        
        return 0;
    }
}
