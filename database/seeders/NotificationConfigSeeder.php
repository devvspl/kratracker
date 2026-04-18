<?php

namespace Database\Seeders;

use App\Models\NotificationConfig;
use Illuminate\Database\Seeder;

class NotificationConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['event_type' => 'task_created', 'email_template' => 'emails.task-created'],
            ['event_type' => 'task_updated', 'email_template' => 'emails.task-updated'],
            ['event_type' => 'task_completed', 'email_template' => 'emails.task-completed'],
            ['event_type' => 'task_overdue', 'email_template' => 'emails.task-overdue'],
            ['event_type' => 'feedback_added', 'email_template' => 'emails.feedback-added'],
        ];

        foreach ($configs as $config) {
            NotificationConfig::create($config);
        }
    }
}
