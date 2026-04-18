<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Not Started', 'color_class' => 'slate', 'sort_order' => 1],
            ['name' => 'In Progress', 'color_class' => 'blue', 'sort_order' => 2],
            ['name' => 'Completed', 'color_class' => 'green', 'sort_order' => 3],
            ['name' => 'On Hold', 'color_class' => 'yellow', 'sort_order' => 4],
            ['name' => 'Cancelled', 'color_class' => 'red', 'sort_order' => 5],
        ];

        foreach ($statuses as $status) {
            TaskStatus::create($status);
        }
    }
}
