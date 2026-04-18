<?php

namespace Database\Seeders;

use App\Models\Logic;
use Illuminate\Database\Seeder;

class LogicSeeder extends Seeder
{
    public function run(): void
    {
        Logic::create([
            'name' => 'Logic 1 - Proportional',
            'description' => 'Score = (achievement/target) * 100, capped at 100%',
            'scoring_type' => 'proportional',
        ]);

        Logic::create([
            'name' => 'Logic 3 - Binary',
            'description' => 'Score = 100 if achievement >= target, else 0',
            'scoring_type' => 'binary',
        ]);
    }
}
