<?php

namespace Database\Seeders;

use App\Models\Kra;
use App\Models\SubKra;
use App\Models\Logic;
use Illuminate\Database\Seeder;

class KraSeeder extends Seeder
{
    public function run(): void
    {
        $logic1 = Logic::where('scoring_type', 'proportional')->first();
        $logic3 = Logic::where('scoring_type', 'binary')->first();

        // KRA 1: Application Development & Enhancements (35%)
        $kra1 = Kra::create([
            'name' => 'Application Development & Enhancements',
            'total_weightage' => 35,
            'description' => 'Development of new features and enhancements',
            'is_active' => true,
        ]);

        SubKra::create([
            'kra_id' => $kra1->id,
            'name' => 'New Development',
            'weightage' => 15,
            'unit' => '%',
            'measure_type' => 'Percentage',
            'logic_id' => $logic1->id,
            'review_period' => 'Quarterly',
            'description' => 'New feature development',
            'is_active' => true,
        ]);

        SubKra::create([
            'kra_id' => $kra1->id,
            'name' => 'Change Request',
            'weightage' => 20,
            'unit' => '%',
            'measure_type' => 'Percentage',
            'logic_id' => $logic1->id,
            'review_period' => 'Monthly',
            'description' => 'Change requests and modifications',
            'is_active' => true,
        ]);

        // KRA 2: Application Support & Maintenance (55%)
        $kra2 = Kra::create([
            'name' => 'Application Support & Maintenance',
            'total_weightage' => 55,
            'description' => 'Support and maintenance activities',
            'is_active' => true,
        ]);

        SubKra::create([
            'kra_id' => $kra2->id,
            'name' => 'Application Stability & Maintenance',
            'weightage' => 15,
            'unit' => '%',
            'measure_type' => 'Percentage',
            'logic_id' => $logic1->id,
            'review_period' => 'Quarterly',
            'description' => 'Application stability and maintenance',
            'is_active' => true,
        ]);

        SubKra::create([
            'kra_id' => $kra2->id,
            'name' => 'User Queries & Team Support',
            'weightage' => 15,
            'unit' => '%',
            'measure_type' => 'Percentage',
            'logic_id' => $logic1->id,
            'review_period' => 'Monthly',
            'description' => 'User support and query resolution',
            'is_active' => true,
        ]);

        SubKra::create([
            'kra_id' => $kra2->id,
            'name' => 'Documentation, Backup & Code Management',
            'weightage' => 15,
            'unit' => 'Day',
            'measure_type' => 'Days',
            'logic_id' => $logic3->id,
            'review_period' => 'Monthly',
            'description' => 'Documentation and code management',
            'is_active' => true,
        ]);

        SubKra::create([
            'kra_id' => $kra2->id,
            'name' => 'Cross-Application Support',
            'weightage' => 10,
            'unit' => '%',
            'measure_type' => 'Percentage',
            'logic_id' => $logic3->id,
            'review_period' => 'Quarterly',
            'description' => 'Support across multiple applications',
            'is_active' => true,
        ]);

        // KRA 3: Learning & Development (10%)
        $kra3 = Kra::create([
            'name' => 'Learning & Development',
            'total_weightage' => 10,
            'description' => 'Professional development and learning',
            'is_active' => true,
        ]);

        SubKra::create([
            'kra_id' => $kra3->id,
            'name' => 'Learning & Development',
            'weightage' => 10,
            'unit' => '%',
            'measure_type' => 'Percentage',
            'logic_id' => $logic1->id,
            'review_period' => 'Annually',
            'description' => 'Learning and skill development',
            'is_active' => true,
        ]);
    }
}
