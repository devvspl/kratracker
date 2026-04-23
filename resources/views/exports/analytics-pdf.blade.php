<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KRA Analytics Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #334155; background: #fff; }

        .header { background-color: #0d9488; color: #fff; padding: 14px 18px; margin-bottom: 14px; }
        .header-brand { font-size: 16px; font-weight: 700; color: #fff; }
        .header-sub   { font-size: 9px; color: #ccfbf1; margin-top: 2px; }
        .header-meta td { padding: 1px 14px 1px 0; font-size: 8px; color: #e0fdf4; }

        .summary-table { width: 100%; border-collapse: separate; border-spacing: 5px; margin: 0 0 14px 0; }
        .summary-card { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 6px; text-align: center; background: #f8fafc; }
        .summary-card .num { font-size: 18px; font-weight: 700; color: #0d9488; }
        .summary-card .lbl { font-size: 8px; color: #64748b; margin-top: 2px; }

        .section-title { font-size: 10px; font-weight: 700; color: #0f766e; background: #f0fdfa; border-left: 3px solid #0d9488; padding: 5px 8px; margin-bottom: 6px; }

        table.data { width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 16px; }
        table.data thead tr { background-color: #0d9488; color: #fff; }
        table.data thead th { padding: 5px 6px; text-align: left; font-weight: 700; white-space: nowrap; }
        table.data tbody tr:nth-child(even) { background-color: #f8fafc; }
        table.data tbody td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }

        .badge { padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: 700; }
        .b-green  { background: #dcfce7; color: #166534; }
        .b-blue   { background: #dbeafe; color: #1e40af; }
        .b-amber  { background: #fef3c7; color: #92400e; }
        .b-slate  { background: #f1f5f9; color: #475569; }
        .b-red    { background: #fee2e2; color: #991b1b; }

        .s-high { color: #16a34a; font-weight: 700; }
        .s-mid  { color: #d97706; font-weight: 700; }
        .s-low  { color: #dc2626; font-weight: 700; }

        .footer { margin-top: 10px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-size: 7px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-brand">{{ config('app.name') }} — Analytics Report</div>
    <div class="header-sub">Performance Management System</div>
    <table style="margin-top:8px;">
        <tr class="header-meta">
            <td><strong style="color:#fff;">Employee:</strong> {{ $user->name }}</td>
            <td><strong style="color:#fff;">Email:</strong> {{ $user->email }}</td>
            <td><strong style="color:#fff;">Role:</strong> {{ $user->roles->first()?->name ?? 'User' }}</td>
            <td><strong style="color:#fff;">Generated:</strong> {{ $date }}</td>
        </tr>
    </table>
</div>

{{-- Summary --}}
@php
    $total     = $workLogs->count();
    $completed = $workLogs->filter(fn($l) => str_contains(optional($l->status)->name ?? '', 'Completed'))->count();
    $pending   = $total - $completed;
    $avgScore  = $total > 0 ? round($workLogs->avg('score_calculated'), 1) : 0;
    $totalHrs  = round($workLogs->sum('actual_duration'), 1);
@endphp

<table class="summary-table">
    <tr>
        <td class="summary-card"><div class="num">{{ $total }}</div><div class="lbl">Tasks Logged</div></td>
        <td class="summary-card"><div class="num" style="color:#16a34a;">{{ $completed }}</div><div class="lbl">Completed</div></td>
        <td class="summary-card"><div class="num" style="color:#d97706;">{{ $pending }}</div><div class="lbl">Pending</div></td>
        <td class="summary-card"><div class="num" style="color:{{ $avgScore >= 70 ? '#16a34a' : ($avgScore >= 40 ? '#d97706' : '#dc2626') }};">{{ $avgScore }}%</div><div class="lbl">Avg Score</div></td>
        <td class="summary-card"><div class="num" style="color:#7c3aed;">{{ $totalHrs }}h</div><div class="lbl">Hours Logged</div></td>
    </tr>
</table>

{{-- Work Logs Detail --}}
<div class="section-title">Work Logs Detail</div>

<table class="data">
    <thead>
        <tr>
            <th>Date</th>
            <th>Title / Description</th>
            <th>KRA / Sub-KRA</th>
            <th>App / Module</th>
            <th>Time</th>
            <th>Dur (T/A)</th>
            <th>Priority</th>
            <th>Test</th>
            <th>Status</th>
            <th>Score</th>
        </tr>
    </thead>
    <tbody>
        @forelse($workLogs as $log)
        @php
            $statusName = optional($log->status)->name ?? '—';
            $badgeClass = match(true) {
                str_contains($statusName, 'Completed')   => 'b-green',
                str_contains($statusName, 'In Progress') => 'b-blue',
                str_contains($statusName, 'On Hold')     => 'b-amber',
                str_contains($statusName, 'Cancelled')   => 'b-red',
                default                                  => 'b-slate',
            };

            $ach   = (float)$log->achievement_value;
            $tgt   = (float)$log->target_value_snapshot;
            $logic = optional($log->subKra->logic);

            $baseScore = 0;
            if ($logic->scoring_type === 'proportional') {
                $baseScore = $tgt > 0 ? min(($ach / $tgt) * 100, 100) : 0;
            } else {
                $baseScore = $ach >= $tgt ? 100 : 0;
            }

            $statusMult    = match(true) {
                str_contains($statusName, 'Completed')   => 1.0,
                str_contains($statusName, 'In Progress') => 0.7,
                str_contains($statusName, 'On Hold')     => 0.4,
                default                                  => 0.0,
            };
            $priorityBonus = $statusMult > 0 ? match((int)(optional($log->priority)->level ?? 0)) { 3=>10, 2=>5, default=>0 } : 0;
            $testBonus     = $statusMult > 0 ? match($log->test_status) { 'Passed'=>5, 'Failed'=>-10, default=>0 } : 0;
            $t = (float)($log->total_duration ?? 0);
            $a = (float)($log->actual_duration ?? 0);
            $durBonus = 0;
            if ($statusMult > 0 && $t > 0 && $a > 0) {
                if ($a <= $t) $durBonus = 5; elseif ($a > $t*1.2) $durBonus = -5;
            }
            $fbs = $log->feedbacks;
            $feedbackBonus = 0;
            if ($statusMult > 0 && $fbs->isNotEmpty()) {
                $avgR = $fbs->avg('rating');
                $feedbackBonus = match(true) { $avgR>=4.5=>10, $avgR>=3.5=>5, $avgR>=2.5=>0, default=>-5 };
            }
            $finalScore = round(max(0, min(100, ($baseScore * $statusMult) + $priorityBonus + $testBonus + $durBonus + $feedbackBonus)), 1);
            $scoreClass = $finalScore >= 70 ? 's-high' : ($finalScore >= 40 ? 's-mid' : 's-low');

            $startTime = $log->start_time ? \Carbon\Carbon::parse($log->start_time)->format('H:i') : '—';
            $endTime   = $log->end_time   ? \Carbon\Carbon::parse($log->end_time)->format('H:i')   : '—';
            $appName_  = optional($log->application)->name ?? '—';
            $modName   = optional($log->module)->name ?? '—';
        @endphp
        <tr>
            <td style="white-space:nowrap;">{{ $log->log_date->format('d M Y') }}</td>
            <td>
                <div style="font-weight:600;">{{ \Illuminate\Support\Str::limit($log->title, 30) }}</div>
                @if($log->description)<div style="color:#94a3b8;font-size:7px;">{{ \Illuminate\Support\Str::limit($log->description, 45) }}</div>@endif
            </td>
            <td>
                <div style="font-weight:600;">{{ optional($log->subKra->kra)->name ?? '—' }}</div>
                <div style="color:#64748b;">{{ optional($log->subKra)->name ?? '—' }}</div>
            </td>
            <td>
                <div>{{ $appName_ }}</div>
                <div style="color:#94a3b8;">{{ $modName }}</div>
            </td>
            <td style="white-space:nowrap;">{{ $startTime }}<br>{{ $endTime }}</td>
            <td style="white-space:nowrap;">{{ $t }}h<br>{{ $a }}h</td>
            <td>{{ optional($log->priority)->name ?? '—' }}</td>
            <td>
                @if($log->test_status)
                <span class="badge {{ $log->test_status === 'Passed' ? 'b-green' : ($log->test_status === 'Failed' ? 'b-red' : 'b-slate') }}">{{ $log->test_status }}</span>
                @else —@endif
            </td>
            <td><span class="badge {{ $badgeClass }}">{{ $statusName }}</span></td>
            <td><span class="{{ $scoreClass }}">{{ $finalScore }}%</span></td>
        </tr>
        @empty
        <tr><td colspan="10" style="text-align:center;padding:14px;color:#94a3b8;">No work logs found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    {{ config('app.name') }} &nbsp;·&nbsp; {{ $user->name }} &nbsp;·&nbsp; Generated on {{ $date }}
</div>

</body>
</html>
