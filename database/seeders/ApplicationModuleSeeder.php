<?php

namespace Database\Seeders;

use App\Models\ApplicationModule;
use Illuminate\Database\Seeder;

class ApplicationModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Generic web development modules (not tied to a specific application)
        $modules = [
            'Authentication & Authorization',
            'User Management',
            'Dashboard & Analytics',
            'API Integration',
            'Reporting & Exports',
            'Notifications',
            'File Upload & Management',
            'Search & Filtering',
            'Settings & Configuration',
            'Audit Logs',
            'Payment & Billing',
            'Email & Messaging',
            'Role & Permission Management',
            'Data Import / Export',
            'Scheduler & Background Jobs',
            'Frontend UI / UX',
            'Database Optimization',
            'Bug Fixes & Patches',
            'Unit & Integration Testing',
            'Deployment & DevOps',
            'Documentation',
            'Performance Optimization',
            'Security Hardening',
            'Third-party Integrations',
            'Mobile Responsiveness',
        ];

        foreach ($modules as $name) {
            ApplicationModule::firstOrCreate(
                ['application_id' => null, 'name' => $name],
                ['is_active' => true]
            );
        }
    }
}
