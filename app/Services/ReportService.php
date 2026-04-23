<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class ReportService
{
    public function sendReport(User $recipient, User $employee, string $type, ?Carbon $from = null, ?Carbon $to = null): bool
    {
        [$from, $to] = $this->resolveDateRange($type, $from, $to);

        $logs = WorkLog::where('user_id', $employee->id)
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->with(['subKra.kra', 'status', 'priority', 'application', 'module', 'feedbacks'])
            ->orderBy('log_date', 'desc')
            ->get();

        $subject = $this->subject($type, $employee, $from, $to);
        $html    = $this->buildHtml($recipient, $employee, $type, $logs, $from, $to);

        try {
            Mail::send([], [], function (Message $mail) use ($recipient, $subject, $html) {
                $mail->to($recipient->email, $recipient->name)
                     ->subject($subject)
                     ->html($html);
            });
            return true;
        } catch (\Throwable $e) {
            \Log::error("Report email failed to {$recipient->email}: " . $e->getMessage());
            return false;
        }
    }

    public function resolveDateRange(string $type, ?Carbon $from = null, ?Carbon $to = null): array
    {
        if ($from && $to) return [$from, $to];

        return match($type) {
            'daily'   => [Carbon::today(), Carbon::today()],
            'weekly'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default   => [Carbon::today(), Carbon::today()],
        };
    }

    private function subject(string $type, User $employee, Carbon $from, Carbon $to): string
    {
        $period = match($type) {
            'daily'   => 'Daily — ' . $from->format('d M Y'),
            'weekly'  => 'Weekly — ' . $from->format('d M') . ' to ' . $to->format('d M Y'),
            'monthly' => 'Monthly — ' . $from->format('F Y'),
            default   => $from->format('d M Y'),
        };
        return "Work Report [{$period}] — {$employee->name}";
    }

    private function buildHtml(User $recipient, User $employee, string $type, $logs, Carbon $from, Carbon $to): string
    {
        $appName     = config('app.name', 'KRA Tracker');
        $appUrl      = rtrim(config('app.url'), '/');
        $total       = $logs->count();
        $completed   = $logs->filter(fn($l) => str_contains(optional($l->status)->name ?? '', 'Completed'))->count();
        $pending     = $logs->filter(fn($l) => in_array(optional($l->status)->name ?? '', ['Not Started', 'In Progress']))->count();
        $totalHrs    = round($logs->sum('actual_duration'), 1);

        $periodLabel = match($type) {
            'daily'   => $from->format('d M Y'),
            'weekly'  => $from->format('d M') . ' – ' . $to->format('d M Y'),
            'monthly' => $from->format('F Y'),
            default   => $from->format('d M Y'),
        };
        $typeLabel = ucfirst($type);

        // ── Build table rows ──────────────────────────────────────────────────
        $rows = '';
        foreach ($logs as $log) {
            $statusName  = optional($log->status)->name   ?? '—';
            $priorityName= optional($log->priority)->name ?? '—';
            $appName_    = optional($log->application)->name ?? '—';
            $modName     = optional($log->module)->name   ?? '—';
            $kra         = optional($log->subKra->kra)->name ?? '—';
            $subKra      = optional($log->subKra)->name   ?? '—';
            $desc        = \Illuminate\Support\Str::limit($log->description ?? '', 60) ?: '—';
            $totalDur    = $log->total_duration  ?? '—';
            $actualDur   = $log->actual_duration ?? '—';
            $startTime   = $log->start_time ? \Carbon\Carbon::parse($log->start_time)->format('H:i') : '—';
            $endTime     = $log->end_time   ? \Carbon\Carbon::parse($log->end_time)->format('H:i')   : '—';

            $statusColor = match(true) {
                str_contains($statusName, 'Completed')   => '#16a34a',
                str_contains($statusName, 'In Progress') => '#2563eb',
                str_contains($statusName, 'On Hold')     => '#d97706',
                str_contains($statusName, 'Cancelled')   => '#dc2626',
                default                                  => '#64748b',
            };

            $rows .= "
            <tr style='border-bottom:1px solid #f1f5f9;vertical-align:top;'>
              <td style='padding:8px 10px;color:#64748b;white-space:nowrap;font-size:11px;'>{$log->log_date->format('d M Y')}</td>
              <td style='padding:8px 10px;font-size:11px;'>
                <div style='font-weight:600;color:#1e293b;'>" . \Illuminate\Support\Str::limit($log->title, 40) . "</div>
                <div style='color:#94a3b8;font-size:10px;margin-top:2px;'>{$desc}</div>
              </td>
              <td style='padding:8px 10px;font-size:11px;color:#334155;'>{$kra}<br><span style='color:#94a3b8;font-size:10px;'>{$subKra}</span></td>
              <td style='padding:8px 10px;font-size:11px;color:#334155;'>{$appName_}<br><span style='color:#94a3b8;font-size:10px;'>{$modName}</span></td>
              <td style='padding:8px 10px;font-size:11px;color:#64748b;white-space:nowrap;'>{$startTime} – {$endTime}</td>
              <td style='padding:8px 10px;font-size:11px;color:#64748b;white-space:nowrap;'>{$totalDur}h / {$actualDur}h</td>
              <td style='padding:8px 10px;font-size:11px;color:#64748b;'>{$priorityName}</td>
              <td style='padding:8px 10px;font-size:11px;'>
                <span style='background:#f1f5f9;color:{$statusColor};padding:2px 7px;border-radius:8px;font-weight:600;font-size:10px;'>{$statusName}</span>
              </td>
            </tr>";
        }

        if (!$rows) {
            $rows = "<tr><td colspan='8' style='padding:20px;text-align:center;color:#94a3b8;font-size:12px;'>No work logs in this period.</td></tr>";
        }

        return "<!DOCTYPE html>
<html>
<body style='margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;'>
<div style='max-width:700px;margin:24px auto;background:#ffffff;border:1px solid #e0e0e0;border-radius:4px;overflow:hidden;'>

  <!-- Header -->
  <div style='background:#0d9488;padding:20px 28px;'>
    <table width='100%' cellpadding='0' cellspacing='0'><tr>
      <td>
        <div style='color:#ffffff;font-size:20px;font-weight:700;letter-spacing:-0.3px;'>{$appName}</div>
        <div style='color:#99f6e4;font-size:11px;margin-top:3px;'>Performance Management</div>
      </td>
      <td style='text-align:right;'>
        <div style='color:#ccfbf1;font-size:11px;'>{$typeLabel} Work Report</div>
        <div style='color:#ffffff;font-size:13px;font-weight:600;margin-top:2px;'>{$periodLabel}</div>
      </td>
    </tr></table>
  </div>

  <!-- Greeting -->
  <div style='padding:20px 28px 0;'>
    <p style='margin:0 0 6px;font-size:14px;color:#1e293b;'>Dear <strong>{$recipient->name}</strong>,</p>
    <p style='margin:0;font-size:13px;color:#475569;line-height:1.6;'>
      Please find below the {$typeLabel} work report for <strong>{$employee->name}</strong> covering the period <strong>{$periodLabel}</strong>.
    </p>
  </div>

  <!-- Summary -->
  <div style='padding:16px 28px;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e2e8f0;border-radius:4px;overflow:hidden;'>
      <tr style='background:#f8fafc;'>
        <td style='padding:12px 16px;text-align:center;border-right:1px solid #e2e8f0;'>
          <div style='font-size:22px;font-weight:700;color:#0d9488;'>{$total}</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;text-transform:uppercase;letter-spacing:0.5px;'>Tasks Logged</div>
        </td>
        <td style='padding:12px 16px;text-align:center;border-right:1px solid #e2e8f0;'>
          <div style='font-size:22px;font-weight:700;color:#16a34a;'>{$completed}</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;text-transform:uppercase;letter-spacing:0.5px;'>Completed</div>
        </td>
        <td style='padding:12px 16px;text-align:center;border-right:1px solid #e2e8f0;'>
          <div style='font-size:22px;font-weight:700;color:#d97706;'>{$pending}</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;text-transform:uppercase;letter-spacing:0.5px;'>Pending</div>
        </td>
        <td style='padding:12px 16px;text-align:center;'>
          <div style='font-size:22px;font-weight:700;color:#7c3aed;'>{$totalHrs}h</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;text-transform:uppercase;letter-spacing:0.5px;'>Hours Logged</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Work Logs Table -->
  <div style='padding:0 28px 20px;'>
    <p style='font-size:12px;font-weight:700;color:#334155;margin:0 0 8px;text-transform:uppercase;letter-spacing:0.5px;border-left:3px solid #0d9488;padding-left:8px;'>Work Log Details</p>
    <div style='overflow-x:auto;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;font-size:11px;border:1px solid #e2e8f0;'>
      <thead>
        <tr style='background:#f1f5f9;'>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;white-space:nowrap;'>Date</th>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;'>Task / Description</th>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;'>KRA / Sub-KRA</th>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;'>App / Module</th>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;white-space:nowrap;'>Time</th>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;white-space:nowrap;'>Dur. (T/A)</th>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;'>Priority</th>
          <th style='padding:8px 10px;text-align:left;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;'>Status</th>
        </tr>
      </thead>
      <tbody>{$rows}</tbody>
    </table>
    </div>
  </div>

  <!-- CTA -->
  <div style='padding:0 28px 20px;text-align:center;'>
    <a href='{$appUrl}/dashboard' style='display:inline-block;padding:10px 24px;background:#0d9488;color:#ffffff;text-decoration:none;border-radius:4px;font-size:13px;font-weight:600;'>View Full Dashboard →</a>
  </div>

  <!-- Sign-off -->
  <div style='padding:16px 28px;border-top:1px solid #f1f5f9;'>
    <p style='margin:0;font-size:13px;color:#475569;line-height:1.7;'>
      Best regards,<br>
      <strong style='color:#1e293b;'>{$appName} System</strong><br>
      <span style='font-size:11px;color:#94a3b8;'>This is an automated {$typeLabel} report. Please do not reply to this email.</span>
    </p>
  </div>

</div>
</body>
</html>";
    }
}
