<?php

namespace Database\Seeders;

use App\Models\ForwardReason;
use Illuminate\Database\Seeder;

class ForwardReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            'Pending dependencies',
            'Awaiting departmental feedback',
            'Resource unavailable',
            'Blocked by another task',
            'Requires more time',
            'Rescheduled by manager',
            'Technical issue',
            'Under review',
            'Waiting for approval',
            'Environment not ready',
        ];

        foreach ($reasons as $reason) {
            ForwardReason::firstOrCreate(
                ['reason' => $reason],
                ['is_active' => true]
            );
        }

        $this->command->info('ForwardReason seeder completed — ' . count($reasons) . ' reasons seeded.');
    }
}
