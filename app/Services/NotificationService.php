<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationConfig;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class NotificationService
{
    /**
     * Create a DB notification and optionally send email.
     */
    public function notify(User $user, string $type, string $message, array $data = []): Notification
    {
        // Avoid duplicate unread notifications of same type+work_log
        $existing = Notification::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_read', false)
            ->when(isset($data['work_log_id']), fn($q) => $q->whereJsonContains('data->work_log_id', $data['work_log_id']))
            ->first();

        if ($existing) {
            // Update message timestamp instead of duplicating
            $existing->update(['message' => $message, 'updated_at' => now()]);
            $notification = $existing;
        } else {
            $notification = Notification::create([
                'user_id' => $user->id,
                'type'    => $type,
                'message' => $message,
                'data'    => $data,
                'is_read' => false,
            ]);
        }

        // Send email if config says so
        $config = NotificationConfig::where('event_type', $type)
            ->where('is_active', true)
            ->where('is_email_enabled', true)
            ->first();

        if ($config) {
            $this->sendEmail($user, $type, $message, $data);
        }

        return $notification;
    }

    private function sendEmail(User $user, string $type, string $message, array $data): void
    {
        try {
            Mail::send([], [], function (Message $mail) use ($user, $type, $message, $data) {
                $mail->to($user->email, $user->name)
                     ->subject($this->emailSubject($type))
                     ->html($this->emailBody($user, $type, $message, $data));
            });
        } catch (\Throwable $e) {
            \Log::error("Notification email failed for user {$user->id}: " . $e->getMessage());
        }
    }

    private function emailSubject(string $type): string
    {
        return match($type) {
            'task_overdue'        => '⚠️ Overdue Task Reminder — KRA Tracker',
            'daily_reminder'      => '📋 Daily Work Log Reminder — KRA Tracker',
            'pending_review'      => '🔔 Tasks Pending Review — KRA Tracker',
            'task_created'        => '✅ Work Log Created — KRA Tracker',
            'task_completed'      => '🎉 Task Completed — KRA Tracker',
            'feedback_added'      => '💬 New Feedback on Your Task — KRA Tracker',
            default               => '🔔 KRA Tracker Notification',
        };
    }

    private function emailBody(User $user, string $type, string $message, array $data): string
    {
        $appName = config('app.name', 'KRA Tracker');
        $appUrl  = config('app.url');
        $link    = isset($data['work_log_id'])
            ? $appUrl . '/work-logs?date_from=' . now()->toDateString() . '&date_to=' . now()->toDateString()
            : $appUrl . '/dashboard';

        $iconMap = [
            'task_overdue'   => '⚠️',
            'daily_reminder' => '📋',
            'pending_review' => '🔔',
            'task_created'   => '✅',
            'task_completed' => '🎉',
            'feedback_added' => '💬',
        ];
        $icon = $iconMap[$type] ?? '🔔';

        return "
        <!DOCTYPE html>
        <html>
        <body style='margin:0;padding:0;background:#f8fafc;font-family:sans-serif;'>
          <div style='max-width:520px;margin:32px auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'>
            <div style='background:linear-gradient(135deg,#0d9488,#0f766e);padding:24px 28px;'>
              <h1 style='margin:0;color:#fff;font-size:18px;font-weight:700;'>{$appName}</h1>
              <p style='margin:4px 0 0;color:#99f6e4;font-size:12px;'>Performance Management System</p>
            </div>
            <div style='padding:28px;'>
              <div style='font-size:32px;margin-bottom:12px;'>{$icon}</div>
              <h2 style='margin:0 0 8px;color:#1e293b;font-size:16px;font-weight:700;'>{$this->emailSubject($type)}</h2>
              <p style='margin:0 0 20px;color:#475569;font-size:14px;line-height:1.6;'>{$message}</p>
              " . (isset($data['title']) ? "<p style='margin:0 0 20px;padding:12px 16px;background:#f1f5f9;border-left:3px solid #0d9488;border-radius:4px;color:#334155;font-size:13px;'><strong>Task:</strong> {$data['title']}</p>" : "") . "
              <a href='{$link}' style='display:inline-block;padding:10px 20px;background:#0d9488;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600;'>View in KRA Tracker →</a>
            </div>
            <div style='padding:16px 28px;border-top:1px solid #f1f5f9;background:#f8fafc;'>
              <p style='margin:0;color:#94a3b8;font-size:11px;'>Hi {$user->name}, this is an automated reminder from {$appName}. You can manage notification preferences in your profile.</p>
            </div>
          </div>
        </body>
        </html>";
    }
}
