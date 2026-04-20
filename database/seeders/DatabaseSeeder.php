<?php

namespace Database\Seeders;

use App\Models\Kra;
use App\Models\Logic;
use App\Models\Priority;
use App\Models\SubKra;
use App\Models\TaskStatus;
use App\Models\Application;
use App\Models\ApplicationModule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Global master data (user_id = null, visible to all) ────────────
        $this->call([
            LogicSeeder::class,
            KraSeeder::class,
            TaskStatusSeeder::class,
            PrioritySeeder::class,
            NotificationConfigSeeder::class,
            RoleSeeder::class,
            ApplicationModuleSeeder::class,
        ]);

        // ── 2. Users ──────────────────────────────────────────────────────────
        $admin = User::factory()->create([
            'name'  => 'Admin',
            'email' => 'admin@performia.com',
        ]);
        $admin->assignRole('Admin');

        $manager = User::factory()->create([
            'name'  => 'Manager',
            'email' => 'manager@performia.com',
        ]);
        $manager->assignRole('Manager');

        // Dev user — gets their own KRA config
        $dev = User::factory()->create([
            'name'               => 'Dev',
            'email'              => 'devendrakumar.vspl@gmail.com',
            'can_manage_own_kra' => true,
        ]);
        $dev->assignRole('Employee');

        // ── 3. Assign global master data to Dev user ──────────────────────────
        // Update all global (user_id = null) records to also be accessible
        // by Dev — we do this by creating user-specific copies for Dev.

        $logic1 = Logic::where('scoring_type', 'proportional')->whereNull('user_id')->first();
        $logic3 = Logic::where('scoring_type', 'binary')->whereNull('user_id')->first();

        // Dev's personal KRAs (user_id = dev->id)
        $kra1 = Kra::create([
            'user_id'         => $dev->id,
            'name'            => 'Application Development & Enhancements',
            'total_weightage' => 35,
            'description'     => 'Development of new features and enhancements',
            'is_active'       => true,
        ]);

        SubKra::create(['kra_id' => $kra1->id, 'name' => 'New Development',   'weightage' => 15, 'unit' => '%', 'measure_type' => 'Percentage', 'logic_id' => $logic1->id, 'review_period' => 'Quarterly', 'description' => 'New feature development',          'is_active' => true]);
        SubKra::create(['kra_id' => $kra1->id, 'name' => 'Change Request',    'weightage' => 20, 'unit' => '%', 'measure_type' => 'Percentage', 'logic_id' => $logic1->id, 'review_period' => 'Monthly',   'description' => 'Change requests and modifications', 'is_active' => true]);

        $kra2 = Kra::create([
            'user_id'         => $dev->id,
            'name'            => 'Application Support & Maintenance',
            'total_weightage' => 55,
            'description'     => 'Support and maintenance activities',
            'is_active'       => true,
        ]);

        SubKra::create(['kra_id' => $kra2->id, 'name' => 'Application Stability & Maintenance',      'weightage' => 15, 'unit' => '%',  'measure_type' => 'Percentage', 'logic_id' => $logic1->id, 'review_period' => 'Quarterly', 'description' => 'Application stability and maintenance', 'is_active' => true]);
        SubKra::create(['kra_id' => $kra2->id, 'name' => 'User Queries & Team Support',              'weightage' => 15, 'unit' => '%',  'measure_type' => 'Percentage', 'logic_id' => $logic1->id, 'review_period' => 'Monthly',   'description' => 'User support and query resolution',     'is_active' => true]);
        SubKra::create(['kra_id' => $kra2->id, 'name' => 'Documentation, Backup & Code Management', 'weightage' => 15, 'unit' => 'Day','measure_type' => 'Days',       'logic_id' => $logic3->id, 'review_period' => 'Monthly',   'description' => 'Documentation and code management',     'is_active' => true]);
        SubKra::create(['kra_id' => $kra2->id, 'name' => 'Cross-Application Support',               'weightage' => 10, 'unit' => '%',  'measure_type' => 'Percentage', 'logic_id' => $logic3->id, 'review_period' => 'Quarterly', 'description' => 'Support across multiple applications',  'is_active' => true]);

        $kra3 = Kra::create([
            'user_id'         => $dev->id,
            'name'            => 'Learning & Development',
            'total_weightage' => 10,
            'description'     => 'Professional development and learning',
            'is_active'       => true,
        ]);

        SubKra::create(['kra_id' => $kra3->id, 'name' => 'Learning & Development', 'weightage' => 10, 'unit' => '%', 'measure_type' => 'Percentage', 'logic_id' => $logic1->id, 'review_period' => 'Annually', 'description' => 'Learning and skill development', 'is_active' => true]);

        // Dev's personal task statuses
        $statuses = [
            ['name' => 'Not Started', 'color_class' => 'slate',  'sort_order' => 1],
            ['name' => 'In Progress', 'color_class' => 'blue',   'sort_order' => 2],
            ['name' => 'Completed',   'color_class' => 'green',  'sort_order' => 3],
            ['name' => 'On Hold',     'color_class' => 'yellow', 'sort_order' => 4],
            ['name' => 'Cancelled',   'color_class' => 'red',    'sort_order' => 5],
        ];
        foreach ($statuses as $s) {
            TaskStatus::create([...$s, 'user_id' => $dev->id]);
        }

        // Dev's personal priorities
        $priorities = [
            ['name' => 'High',   'color_class' => 'red',    'level' => 3],
            ['name' => 'Medium', 'color_class' => 'yellow', 'level' => 2],
            ['name' => 'Low',    'color_class' => 'green',  'level' => 1],
            ['name' => 'Common', 'color_class' => 'slate',  'level' => 0],
        ];
        foreach ($priorities as $p) {
            Priority::create([...$p, 'user_id' => $dev->id]);
        }

        // Dev's personal application
        $app = Application::create([
            'user_id'    => $dev->id,
            'name'       => 'KRA Tracker',
            'tech_stack' => 'Laravel, Alpine.js, Tailwind CSS',
            'description'=> 'Internal performance management system',
            'is_active'  => true,
        ]);

        // Dev's personal modules for that app
        $devModules = ['Dashboard', 'Work Logs', 'KRA Management', 'Reports', 'Notifications', 'User Management'];
        foreach ($devModules as $mod) {
            ApplicationModule::create([
                'user_id'        => $dev->id,
                'application_id' => $app->id,
                'name'           => $mod,
                'is_active'      => true,
            ]);
        }
    }
}
