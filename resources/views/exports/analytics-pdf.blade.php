<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KRA Analytics Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #334155; background: #fff; }

        /* ── Header ── */
        .header {
            background-color: #0d9488;
            color: #ffffff;
            padding: 16px 20px;
            margin-bottom: 16px;
        }
        .header-inner { width: 100%; }
        .header-brand { font-size: 18px; font-weight: 700; color: #fff; }
        .header-sub   { font-size: 10px; color: #ccfbf1; margin-top: 2px; }
        .header-meta  { margin-top: 10px; font-size: 9px; color: #e0fdf4; }
        .header-meta td { padding: 1px 16px 1px 0; }

        /* ── Summary cards (table-based for DomPDF) ── */
        .summary-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 0 0 16px 0; }
        .summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 8px;
            text-align: center;
            background: #f8fafc;
            width: 20%;
        }
        .summary-card .num { font-size: 20px; font-weight: 700; color: #0d9488; }
        .summary-card .lbl { font-size: 9px; color: #64748b; margin-top: 3px; }

        /* ── Section title ── */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #0f766e;
            background: #f0fdfa;
            border-left: 4px solid #0d9488;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        /* ── Table ── */
        table.data { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 20px; }
        table.data thead tr { background-color: #0d9488; color: #ffffff; }
        table.data thead th { padding: 6px 7px; text-align: left; font-weight: 700; white-space: nowrap; }
        table.data tbody tr:nth-child(even) { background-color: #f8fafc; }
        table.data tbody td { padding: 5px 7px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }

        /* ── Badges ── */
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: 700; }
        .b-green  { background: #dcfce7; color: #166534; }
        .b-blue   { background: #dbeafe; color: #1e40af; }
        .b-amber  { background: #fef3c7; color: #92400e; }
        .b-slate  { background: #f1f5f9; color: #475569; }
        .b-red    { background: #fee2e2; color: #991b1b; }

        /* ── Score colors ── */
        .s-high { color: #16a34a; font-weight: 700; }
        .s-mid  { color: #d97706; font-weight: 700; }
        .s-low  { color: #dc2626; font-weight: 700; }

        /* ── Score breakdown box ── */
        .breakdown { font-size: 8px; color: #64748b; margin-top: 3px; }
        .breakdown span { margin-right: 4px; }
        .pos { color: #16a34a; }
        .neg { color: #dc2626; }

        /* ── Footer ── */
        .footer {
            margin-top: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-brand">Performia — Analytics Report</div>
    <div class="header-sub">Performance Management System</div>
    <table class="header-meta">
        <tr>
            <td><strong style="color:#fff;">Employee:</strong> {{ $user->name }}</td>
            <td><strong style="color:#fff;">Email:</strong> {{ $user->email }}</td>
            <td><strong style="color:#fff;">Role:</strong> {{ $user->roles->first()?->name ?? 'User' }}</td>
            <td><strong style="color:#fff;">Generated:</strong> {{ $date }}</td>
        </tr>
    </table>
</div>

{{-- ── Summary cards ── --}}
@php
    $total     = $workLogs->count();
    $completed = $workLogs->filter(fn($l) => str_contains(optional($l->status)->name ?? '', 'Completed'))->count();
    $pending   = $total - $completed;
    $avgScore  = $total > 0 ? round($workLogs->avg('score_calculated'), 1) : 0;
    $totalHrs  = round($workLogs->sum('actual_duration'), 1);
@endphp

<table class="summary-table">
    <tr>
        <td class="summary-card">
            <div class="num">{{ $total }}</div>
            <div class="lbl">Tasks Logged</div>
        </td>
        <td class="summary-card">
            <div class="num" style="color:#16a34a;">{{ $completed }}</div>
            <div class="lbl">Completed</div>
        </td>
        <td class="summary-card">
            <div class="num" style="color:#d97706;">{{ $pending }}</div>
            <div class="lbl">Pending</div>
        </td>
        <td class="summary-card">
            <div class="num {{ $avgScore >= 70 ? '' : ($avgScore >= 40 ? '' : '') }}"
                 style="color:{{ $avgScore >= 70 ? '#16a34a' : ($avgScore >= 40 ? '#d97706' : '#dc2626') }}">
                {{ $avgScore }}%
            </div>
            <div class="lbl">Avg Score</div>
        </td>
        <td class="summary-card">
            <div class="num" style="color:#7c3aed;">{{ $totalHrs }}h</div>
            <div class="lbl">Hours Logged</div>
        </td>
    </tr>
</table>

{{-- ── Work Logs Detail ── --}}
<div class="section-title">Work Logs Detail</div>

<table class="data">
    <thead>
        <tr>
            <th>Date</th>
            <th>Title</th>
            <th>KRA / Sub-KRA</th>
            <th>Ach / Target</th>
            <th>Base Score</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Test</th>
            <th>Dur (T/A)</th>
            <th>Feedback</th>
            <th>Final Score</th>
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

            $ach    = (float) $log->achievement_value;
            $tgt    = (float) $log->target_value_snapshot;
            $logic  = optional($log->subKra->logic);

            $baseScore = 0;
            if ($logic->scoring_type === 'proportional') {
                $baseScore = $tgt > 0 ? min(($ach / $tgt) * 100, 100) : 0;
            } else {
                $baseScore = $ach >= $tgt ? 100 : 0;
            }

            $statusMult = match(true) {
                str_contains($statusName, 'Completed')   => 1.0,
                str_contains($statusName, 'In Progress') => 0.7,
                str_contains($statusName, 'On Hold')     => 0.4,
                default                                  => 0.0,
            };

            $priorityBonus = 0;
            $testBonus     = 0;
            $durBonus      = 0;
            $feedbackBonus = 0;

            if ($statusMult > 0) {
                $priorityBonus = match((int)(optional($log->priority)->level ?? 0)) { 3=>10, 2=>5, default=>0 };
                $testBonus     = match($log->test_status) { 'Passed'=>5, 'Failed'=>-10, default=>0 };
                $t = (float)($log->total_duration ?? 0);
                $a = (float)($log->actual_duration ?? 0);
                if ($t > 0 && $a > 0) {
                    if ($a <= $t)      $durBonus = 5;
                    elseif ($a > $t*1.2) $durBonus = -5;
                }
                $fbs = $log->feedbacks;
                if ($fbs->isNotEmpty()) {
                    $avgR = $fbs->avg('rating');
                    $feedbackBonus = match(true) { $avgR>=4.5=>10, $avgR>=3.5=>5, $avgR>=2.5=>0, default=>-5 };
                }
            }

            $finalScore = round(max(0, min(100, ($baseScore * $statusMult) + $priorityBonus + $testBonus + $durBonus + $feedbackBonus)), 1);
            $scoreClass = $finalScore >= 70 ? 's-high' : ($finalScore >= 40 ? 's-mid' : 's-low');
        @endphp
        <tr>
            <td style="white-space:nowrap;">{{ $log->log_date->format('d M Y') }}</td>
            <td>{{ \Illuminate\Support\Str::limit($log->title, 28) }}</td>
            <td>
                <div style="font-weight:600;">{{ optional($log->subKra->kra)->name ?? '—' }}</div>
                <div style="color:#64748b;">{{ optional($log->subKra)->name ?? '—' }}</div>
            </td>
            <td style="white-space:nowrap;">
                {{ $ach }} / {{ $tgt }}
                <div style="color:#0d9488; font-weight:600;">
                    {{ $tgt > 0 ? round(($ach/$tgt)*100, 1) : 0 }}%
                </div>
            </td>
            <td style="white-space:nowrap;">
                {{ round($baseScore, 1) }}%
                <div style="color:#64748b;">×{{ $statusMult }}</div>
            </td>
            <td><span class="badge {{ $badgeClass }}">{{ $statusName }}</span></td>
            <td>{{ optional($log->priority)->name ?? '—' }}</td>
            <td>
                @if($log->test_status)
                <span class="badge {{ $log->test_status === 'Passed' ? 'b-green' : ($log->test_status === 'Failed' ? 'b-red' : 'b-slate') }}">
                    {{ $log->test_status }}
                </span>
                @else —@endif
            </td>
            <td style="white-space:nowrap;">
                {{ $log->total_duration ?? 0 }}h / {{ $log->actual_duration ?? 0 }}h
            </td>
            <td>
                @if($log->feedbacks->isNotEmpty())
                    ★ {{ round($log->feedbacks->avg('rating'), 1) }}
                @else —@endif
            </td>
            <td>
                <span class="{{ $scoreClass }}" style="font-size:11px;">{{ $finalScore }}%</span>
                <div class="breakdown">
                    @if($priorityBonus != 0)<span class="{{ $priorityBonus > 0 ? 'pos' : 'neg' }}">P:{{ $priorityBonus > 0 ? '+' : '' }}{{ $priorityBonus }}</span>@endif
                    @if($testBonus != 0)<span class="{{ $testBonus > 0 ? 'pos' : 'neg' }}">T:{{ $testBonus > 0 ? '+' : '' }}{{ $testBonus }}</span>@endif
                    @if($durBonus != 0)<span class="{{ $durBonus > 0 ? 'pos' : 'neg' }}">D:{{ $durBonus > 0 ? '+' : '' }}{{ $durBonus }}</span>@endif
                    @if($feedbackBonus != 0)<span class="{{ $feedbackBonus > 0 ? 'pos' : 'neg' }}">F:{{ $feedbackBonus > 0 ? '+' : '' }}{{ $feedbackBonus }}</span>@endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="11" style="text-align:center; padding:16px; color:#94a3b8;">
                No work logs found for this period.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Performia &nbsp;·&nbsp; {{ $user->name }} &nbsp;·&nbsp; Generated on {{ $date }}
</div>

</body>
</html>
