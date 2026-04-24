<?php

namespace App\Console\Commands;

use App\Models\ReportConfig;
use App\Models\EmailContact;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReports extends Command
{
    protected $signature   = 'kra:send-reports {type? : daily|weekly|monthly (default: auto)}';
    protected $description = 'Send scheduled work status reports to managers/admins';

    public function handle(ReportService $reporter): int
    {
        $type = $this->argument('type') ?? $this->autoType();

        $this->info("Sending {$type} reports...");

        $configs = ReportConfig::where('report_type', $type)
            ->where('is_active', true)
            ->with('recipient')
            ->get();

        $sent = 0;

        foreach ($configs as $config) {
            $recipient = $config->recipient;
            if (!$recipient) continue;

            // Send for all employees
            $employees = User::role(['Employee', 'Manager'])->get();

            foreach ($employees as $employee) {
                if (!$employee) continue;
                $ok = $reporter->sendReport($recipient, $employee, $type);
                if ($ok) {
                    $sent++;
                    $this->line("  ✓ Sent {$type} report for {$employee->name} → {$recipient->email}");
                }
            }

            $config->update(['last_sent_at' => now()]);
        }

        $this->info("Done. {$sent} report(s) sent.");

        // Also send to external contacts subscribed to this report type
        $this->sendToContacts($type, $reporter);

        return 0;
    }

    private function sendToContacts(string $type, ReportService $reporter): void
    {
        $field = "notify_on_{$type}_report";
        $contacts = EmailContact::where($field, true)->where('is_active', true)->get();
        if ($contacts->isEmpty()) return;

        $employees = User::role(['Employee', 'Manager'])->get();

        foreach ($contacts as $contact) {
            $pseudo        = new User();
            $pseudo->name  = $contact->name;
            $pseudo->email = $contact->email;

            foreach ($employees as $employee) {
                $ok = $reporter->sendReport($pseudo, $employee, $type);
                if ($ok) $this->line("  ✓ Contact report: {$employee->name} → {$contact->email}");
            }
        }
    }

    private function autoType(): string
    {
        $now = Carbon::now();
        // Monthly: 1st of month at 08:00
        if ($now->day === 1) return 'monthly';
        // Weekly: Monday
        if ($now->isMonday()) return 'weekly';
        // Otherwise daily
        return 'daily';
    }
}
