<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Not Started', 'sort_order' => 1],
            ['name' => 'In Progress', 'sort_order' => 2],
            ['name' => 'Completed',   'sort_order' => 3],
            ['name' => 'On Hold',     'sort_order' => 4],
            ['name' => 'Cancelled',   'sort_order' => 5],
        ];

        foreach ($statuses as $status) {
            TaskStatus::create($status);
        }
    }
}
