<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['name' => 'High'],
            ['name' => 'Medium'],
            ['name' => 'Low'],
            ['name' => 'Common'],
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}
