<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class ReportService
{
    /**
     * Send a report email for a specific employee to a recipient.
     */
    public function sendReport(User $recipient, User $employee, string $type, ?Carbon $from = null, ?Carbon $to = null): bool
    {
        [$from, $to] = $this->resolveDateRange($type, $from, $to);

        $logs = WorkLog::where('user_id', $employee->id)
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->with(['subKra.kra', 'status', 'priority', 'application', 'feedbacks'])
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
        return "📊 KRA Work Report [{$period}] — {$employee->name}";
    }

    private function buildHtml(User $recipient, User $employee, string $type, $logs, Carbon $from, Carbon $to): string
    {
        $appName  = config('app.name', 'Performia');
        $appUrl   = rtrim(config('app.url'), '/');
        $total    = $logs->count();
        $completed = $logs->filter(fn($l) => str_contains(optional($l->status)->name ?? '', 'Completed'))->count();
        $pending  = $logs->filter(fn($l) => in_array(optional($l->status)->name ?? '', ['Not Started', 'In Progress']))->count();
        $avgScore = $total > 0 ? round($logs->avg('score_calculated'), 1) : 0;
        $totalHrs = round($logs->sum('actual_duration'), 1);

        $periodLabel = match($type) {
            'daily'   => $from->format('d M Y'),
            'weekly'  => $from->format('d M') . ' – ' . $to->format('d M Y'),
            'monthly' => $from->format('F Y'),
            default   => $from->format('d M Y'),
        };

        $typeLabel = ucfirst($type);
        $scoreColor = $avgScore >= 70 ? '#16a34a' : ($avgScore >= 40 ? '#d97706' : '#dc2626');

        // Build rows
        $rows = '';
        foreach ($logs as $log) {
            $statusName = optional($log->status)->name ?? '—';
            $statusColor = match(true) {
                str_contains($statusName, 'Completed')   => '#16a34a',
                str_contains($statusName, 'In Progress') => '#2563eb',
                str_contains($statusName, 'On Hold')     => '#d97706',
                default                                  => '#64748b',
            };
            $scoreVal   = number_format($log->score_calculated, 1);
            $sc         = $log->score_calculated >= 70 ? '#16a34a' : ($log->score_calculated >= 40 ? '#d97706' : '#dc2626');
            $kra        = optional($log->subKra->kra)->name ?? '—';
            $subKra     = optional($log->subKra)->name ?? '—';
            $priority   = optional($log->priority)->name ?? '—';
            $dur        = ($log->actual_duration ?? 0) . 'h';
            $rows .= "
            <tr style='border-bottom:1px solid #f1f5f9;'>
              <td style='padding:8px 10px;color:#64748b;white-space:nowrap;font-size:12px;'>{$log->log_date->format('d M')}</td>
              <td style='padding:8px 10px;font-size:12px;'>
                <div style='font-weight:600;color:#1e293b;'>" . \Illuminate\Support\Str::limit($log->title, 40) . "</div>
                <div style='color:#94a3b8;font-size:11px;'>{$kra} › {$subKra}</div>
              </td>
              <td style='padding:8px 10px;font-size:11px;'>
                <span style='background:#f1f5f9;color:{$statusColor};padding:2px 8px;border-radius:10px;font-weight:600;'>{$statusName}</span>
              </td>
              <td style='padding:8px 10px;font-size:11px;color:#64748b;'>{$priority}</td>
              <td style='padding:8px 10px;font-size:11px;color:#64748b;'>{$dur}</td>
              <td style='padding:8px 10px;font-size:12px;font-weight:700;color:{$sc};'>{$scoreVal}%</td>
            </tr>";
        }

        if (!$rows) {
            $rows = "<tr><td colspan='6' style='padding:20px;text-align:center;color:#94a3b8;font-size:12px;'>No work logs in this period.</td></tr>";
        }

        return "<!DOCTYPE html>
<html>
<body style='margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;'>
<div style='max-width:640px;margin:24px auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'>

  <!-- Header -->
  <div style='background:#0d9488;padding:20px 24px;'>
    <table width='100%'><tr>
      <td>
        <div style='color:#fff;font-size:18px;font-weight:700;'>{$appName}</div>
        <div style='color:#99f6e4;font-size:11px;margin-top:2px;'>Performance Management System</div>
      </td>
      <td style='text-align:right;'>
        <div style='color:#ccfbf1;font-size:11px;'>{$typeLabel} Report</div>
        <div style='color:#fff;font-size:12px;font-weight:600;'>{$periodLabel}</div>
      </td>
    </tr></table>
  </div>

  <!-- Employee info -->
  <div style='padding:16px 24px;background:#f0fdfa;border-bottom:1px solid #e2e8f0;'>
    <table width='100%'><tr>
      <td>
        <div style='font-size:13px;font-weight:700;color:#0f766e;'>{$employee->name}</div>
        <div style='font-size:11px;color:#64748b;'>{$employee->email}</div>
      </td>
      <td style='text-align:right;font-size:11px;color:#64748b;'>
        Prepared for: <strong style='color:#334155;'>{$recipient->name}</strong>
      </td>
    </tr></table>
  </div>

  <!-- Summary stats -->
  <div style='padding:16px 24px;border-bottom:1px solid #f1f5f9;'>
    <table width='100%' cellspacing='0' cellpadding='0'>
      <tr>
        <td style='text-align:center;padding:10px;border-right:1px solid #f1f5f9;'>
          <div style='font-size:22px;font-weight:700;color:#0d9488;'>{$total}</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;'>Tasks Logged</div>
        </td>
        <td style='text-align:center;padding:10px;border-right:1px solid #f1f5f9;'>
          <div style='font-size:22px;font-weight:700;color:#16a34a;'>{$completed}</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;'>Completed</div>
        </td>
        <td style='text-align:center;padding:10px;border-right:1px solid #f1f5f9;'>
          <div style='font-size:22px;font-weight:700;color:#d97706;'>{$pending}</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;'>Pending</div>
        </td>
        <td style='text-align:center;padding:10px;border-right:1px solid #f1f5f9;'>
          <div style='font-size:22px;font-weight:700;color:{$scoreColor};'>{$avgScore}%</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;'>Avg Score</div>
        </td>
        <td style='text-align:center;padding:10px;'>
          <div style='font-size:22px;font-weight:700;color:#7c3aed;'>{$totalHrs}h</div>
          <div style='font-size:10px;color:#64748b;margin-top:2px;'>Hours Logged</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Work logs table -->
  <div style='padding:16px 24px;'>
    <div style='font-size:12px;font-weight:700;color:#334155;margin-bottom:10px;border-left:3px solid #0d9488;padding-left:8px;'>Work Logs Detail</div>
    <table width='100%' cellspacing='0' cellpadding='0' style='border-collapse:collapse;'>
      <thead>
        <tr style='background:#f8fafc;'>
          <th style='padding:8px 10px;text-align:left;font-size:10px;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;'>DATE</th>
          <th style='padding:8px 10px;text-align:left;font-size:10px;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;'>TASK</th>
          <th style='padding:8px 10px;text-align:left;font-size:10px;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;'>STATUS</th>
          <th style='padding:8px 10px;text-align:left;font-size:10px;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;'>PRIORITY</th>
          <th style='padding:8px 10px;text-align:left;font-size:10px;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;'>DUR.</th>
          <th style='padding:8px 10px;text-align:left;font-size:10px;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;'>SCORE</th>
        </tr>
      </thead>
      <tbody>{$rows}</tbody>
    </table>
  </div>

  <!-- CTA -->
  <div style='padding:16px 24px;border-top:1px solid #f1f5f9;text-align:center;'>
    <a href='{$appUrl}/dashboard' style='display:inline-block;padding:10px 24px;background:#0d9488;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600;'>View Full Dashboard →</a>
  </div>

  <!-- Footer -->
  <div style='padding:12px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;text-align:center;'>
    <p style='margin:0;color:#94a3b8;font-size:10px;'>This is an automated {$typeLabel} report from {$appName}. Sent to {$recipient->name}.</p>
  </div>

</div>
</body>
</html>";
    }
}
