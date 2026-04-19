<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LogicSeeder::class,
            KraSeeder::class,
            TaskStatusSeeder::class,
            PrioritySeeder::class,
            NotificationConfigSeeder::class,
            RoleSeeder::class,
            ApplicationModuleSeeder::class,
        ]);

        // Create a test admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('Admin');

        // Create a test manager user
        $manager = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
        ]);
        $manager->assignRole('Manager');

        // Create a test employee user
        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
        ]);
        $employee->assignRole('Employee');
    }
}
