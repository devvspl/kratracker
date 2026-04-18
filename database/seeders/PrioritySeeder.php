<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['name' => 'High', 'color_class' => 'red', 'level' => 3],
            ['name' => 'Medium', 'color_class' => 'yellow', 'level' => 2],
            ['name' => 'Low', 'color_class' => 'green', 'level' => 1],
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}
