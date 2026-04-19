@extends('layouts.app')
@section('content')

<div x-data="{
    activeTab: '{{ request("tab", "overview") }}',
    filters: {
        date_from: '{{ $dateFrom->toDateString() }}',
        date_to:   '{{ $dateTo->toDateString() }}'
    },
    applyFilters() {
        const p = new URLSearchParams();
        if (this.filters.date_from) p.append('date_from', this.filters.date_from);
        if (this.filters.date_to)   p.append('date_to',   this.filters.date_to);
        p.append('tab', this.activeTab);
        window.location.href = '{{ route("dashboard") }}?' + p.toString();
    }
}">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
        <div>
            <h2 class="text-base font-bold text-slate-800">Dashboard</h2>
            <p class="text-xs text-slate-500 mt-0.5">Performance overview for the selected period</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1.5">
                <label class="text-xs font-medium text-slate-500 whitespace-nowrap">From</label>
                <input type="text" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" x-model="filters.date_from"
                    class="w-32 px-2.5 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer bg-white">
            </div>
            <div class="flex items-center gap-1.5">
                <label class="text-xs font-medium text-slate-500 whitespace-nowrap">To</label>
                <input type="text" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" x-model="filters.date_to"
                    class="w-32 px-2.5 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer bg-white">
            </div>
            <button @click="applyFilters()" class="px-3 py-2 bg-slate-700 text-white text-xs font-medium rounded-lg hover:bg-slate-800 transition-colors shadow-sm">Go</button>
            <a href="{{ route('dashboard') }}" class="px-3 py-2 bg-white border border-slate-300 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition-colors shadow-sm">Clear</a>
            <div class="w-px h-6 bg-slate-200"></div>
            <a href="{{ route('work-logs.index') }}" class="px-3 py-2 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 flex items-center gap-1.5 shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Work Logs
            </a>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 mb-5 bg-white border border-slate-200 rounded-lg p-1 w-fit shadow-sm">
        <button @click="activeTab = 'overview'"
            :class="activeTab === 'overview' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
            class="px-4 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            Overview
        </button>
        <button @click="activeTab = 'matrix'"
            :class="activeTab === 'matrix' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
            class="px-4 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            KRA Matrix
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- TAB 1: OVERVIEW                                        --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 'overview'" x-transition.opacity.duration.200ms style="display:none;">

    {{-- Row 1: KRA Score + all 4 stat cards in one row --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-4">

        {{-- KRA Score — spans 2 cols --}}
        <div class="col-span-2 bg-gradient-to-br from-teal-500 to-teal-700 rounded-xl p-5 text-white shadow-sm">
            <div class="flex items-start gap-6">
                <div class="shrink-0">
                    <p class="text-5xl font-bold leading-none">{{ number_format($overallScore, 1) }}<span class="text-2xl font-semibold">%</span></p>
                    <p class="text-xs text-teal-100 mt-1.5">Avg Task Score</p>
                    <p class="text-xs text-teal-200 mt-0.5">{{ $tasksLogged }} task{{ $tasksLogged != 1 ? 's' : '' }} in period</p>
                </div>
                <div class="flex-1 space-y-2 pt-1 text-xs min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-teal-100 w-32 shrink-0">Task Completion</span>
                        <div class="flex-1 bg-white/20 rounded-full h-1.5">
                            <div class="bg-white h-1.5 rounded-full" style="width:{{ $scoreFactors['completion_rate'] }}%"></div>
                        </div>
                        <span class="text-white font-semibold w-10 text-right">{{ $scoreFactors['completion_rate'] }}%</span>
                    </div>
                    @if($scoreFactors['test_pass_rate'] !== null)
                    <div class="flex items-center gap-2">
                        <span class="text-teal-100 w-32 shrink-0">Test Pass Rate</span>
                        <div class="flex-1 bg-white/20 rounded-full h-1.5">
                            <div class="bg-white h-1.5 rounded-full" style="width:{{ $scoreFactors['test_pass_rate'] }}%"></div>
                        </div>
                        <span class="text-white font-semibold w-10 text-right">{{ $scoreFactors['test_pass_rate'] }}%</span>
                    </div>
                    @endif
                    @if($scoreFactors['duration_eff_pct'] !== null)
                    <div class="flex items-center gap-2">
                        <span class="text-teal-100 w-32 shrink-0">Duration Efficiency</span>
                        <div class="flex-1 bg-white/20 rounded-full h-1.5">
                            <div class="bg-white h-1.5 rounded-full" style="width:{{ $scoreFactors['duration_eff_pct'] }}%"></div>
                        </div>
                        <span class="text-white font-semibold w-10 text-right">{{ $scoreFactors['duration_eff_pct'] }}%</span>
                    </div>
                    @endif
                    @if($scoreFactors['avg_feedback'] > 0)
                    <div class="flex items-center gap-2">
                        <span class="text-teal-100 w-32 shrink-0">Avg Feedback</span>
                        <div class="flex-1 bg-white/20 rounded-full h-1.5">
                            <div class="bg-white h-1.5 rounded-full" style="width:{{ ($scoreFactors['avg_feedback'] / 5) * 100 }}%"></div>
                        </div>
                        <span class="text-white font-semibold w-10 text-right">{{ $scoreFactors['avg_feedback'] }}/5</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tasks Logged --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-medium">Total</span>
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ $tasksLogged }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Tasks Logged</p>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                In selected period
            </div>
        </div>

        {{-- Completed --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if($tasksLogged > 0)
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                    {{ round(($tasksCompleted / $tasksLogged) * 100) }}%
                </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ $tasksCompleted }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Completed</p>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-1">
                <div class="bg-green-400 h-1 rounded-full" style="width:{{ $tasksLogged > 0 ? round(($tasksCompleted / $tasksLogged) * 100) : 0 }}%"></div>
            </div>
            <div class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Of total logged
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if($tasksLogged > 0)
                <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                    {{ round(($pendingTasks / $tasksLogged) * 100) }}%
                </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ $pendingTasks }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Pending / In Progress</p>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-1">
                <div class="bg-amber-400 h-1 rounded-full" style="width:{{ $tasksLogged > 0 ? round(($pendingTasks / $tasksLogged) * 100) : 0 }}%"></div>
            </div>
            <div class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Awaiting completion
            </div>
        </div>

        {{-- Hours --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if($tasksLogged > 0)
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">
                    {{ number_format($totalHours / $tasksLogged, 1) }}h/task
                </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($totalHours, 1) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Hours Logged</p>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Actual duration total
            </div>
        </div>

    </div>

    {{-- Row 2: Score Factors full width --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm mb-5">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Score Factors</p>
        <div class="grid grid-cols-1 divide-y divide-slate-100">
            <div class="flex items-center gap-3 py-2">
                <span class="w-2 h-2 rounded-full bg-teal-500 shrink-0"></span>
                <span class="text-xs font-medium text-slate-600 w-28 shrink-0">Task Status</span>
                <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span>Completed <strong class="text-slate-700">×1.0</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>In Progress <strong class="text-slate-700">×0.7</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>On Hold <strong class="text-slate-700">×0.4</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Not Started <strong class="text-slate-700">×0</strong></span>
                </div>
            </div>
            <div class="flex items-center gap-3 py-2">
                <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
                <span class="text-xs font-medium text-slate-600 w-28 shrink-0">Priority</span>
                <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span>High <strong class="text-green-600">+10 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Medium <strong class="text-green-600">+5 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Low <strong class="text-slate-400">+0</strong></span>
                </div>
            </div>
            <div class="flex items-center gap-3 py-2">
                <span class="w-2 h-2 rounded-full bg-indigo-400 shrink-0"></span>
                <span class="text-xs font-medium text-slate-600 w-28 shrink-0">Testing</span>
                <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span>Passed <strong class="text-green-600">+5 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Failed <strong class="text-red-500">−10 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Skipped / Pending <strong class="text-slate-400">+0</strong></span>
                </div>
            </div>
            <div class="flex items-center gap-3 py-2">
                <span class="w-2 h-2 rounded-full bg-purple-400 shrink-0"></span>
                <span class="text-xs font-medium text-slate-600 w-28 shrink-0">Duration</span>
                <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span>On-time or early <strong class="text-green-600">+5 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>&gt;20% over estimate <strong class="text-red-500">−5 pts</strong></span>
                </div>
            </div>
            <div class="flex items-center gap-3 py-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                <span class="text-xs font-medium text-slate-600 w-28 shrink-0">Feedback</span>
                <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span>≥4.5 <strong class="text-green-600">+10 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>≥3.5 <strong class="text-green-600">+5 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>&lt;2.5 <strong class="text-red-500">−5 pts</strong></span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-400">Max total: 100</span>
                </div>
            </div>
        </div>
    </div>
    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
        {{-- Sub-KRA Score Chart --}}
        <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-0.5">Sub-KRA Performance</h3>
            <p class="text-xs text-slate-500 mb-4">Score distribution across Sub-KRAs</p>
            <div style="position:relative; height:{{ max(120, $kraScores->count() * 44) }}px">
                <canvas id="kraScoreChart"></canvas>
            </div>
        </div>

        {{-- Daily Trend Chart --}}
        <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-0.5">Daily Work Log Trend</h3>
            <p class="text-xs text-slate-500 mb-4">Tasks logged per day in selected period</p>
            <div style="position:relative; height:{{ max(120, $kraScores->count() * 44) }}px">
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Logs + Status Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

        {{-- Recent Work Logs --}}
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Recent Work Logs</h3>
                <a href="{{ route('work-logs.index', ['date_from' => $dateFrom->toDateString(), 'date_to' => $dateTo->toDateString()]) }}"
                   class="text-xs text-teal-600 hover:text-teal-700 font-medium">View all →</a>
            </div>
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Date</th>
                        <th class="px-4 py-2.5 text-left">Title</th>
                        <th class="px-4 py-2.5 text-left">Sub-KRA</th>
                        <th class="px-4 py-2.5 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap">{{ $log->log_date->format('d M') }}</td>
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-slate-700 truncate max-w-[180px]">{{ $log->title }}</p>
                            @if($log->application)
                                <p class="text-slate-400 truncate">{{ $log->application->name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap">{{ $log->subKra->name }}</td>
                        <td class="px-4 py-2.5">
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-{{ $log->status->color_class }}-100 text-{{ $log->status->color_class }}-700">
                                {{ $log->status->name }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No logs in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Status Breakdown + Exports --}}
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Status Breakdown</h3>
            @php $total = $statusBreakdown->sum(); @endphp
            @forelse($statusBreakdown as $status => $count)
                @php $pct = $total > 0 ? round(($count / $total) * 100) : 0; @endphp
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-slate-600 font-medium">{{ $status }}</span>
                        <span class="text-xs text-slate-500">{{ $count }} <span class="text-slate-400">({{ $pct }}%)</span></span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-teal-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-4">No data for this period.</p>
            @endforelse

            <div class="mt-5 pt-4 border-t border-slate-100 space-y-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Exports</p>
                <a href="{{ route('export.work-logs') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Work Logs (Excel)
                </a>
                <a href="{{ route('export.kra-summary') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    KRA Summary (Excel)
                </a>
                <a href="{{ route('export.analytics-pdf') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Analytics (PDF)
                </a>
            </div>
        </div>
    </div>

    </div>{{-- end Tab 1: Overview --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- TAB 2: KRA MATRIX                                      --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 'matrix'" x-transition.opacity.duration.200ms style="display:none;">

    @foreach($kraMatrix as $kra)
    <div class="mb-6 bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">

        {{-- KRA Header --}}
        <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">{{ $kra['name'] }}</h3>
                    @if($kra['description'])
                        <p class="text-xs text-slate-400 mt-0.5">{{ $kra['description'] }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="text-slate-500">Weightage: <span class="font-semibold text-slate-700">{{ $kra['weightage'] }}%</span></span>
                @if($kra['avg_score'] > 0)
                <span class="px-2.5 py-1 rounded-full font-semibold
                    {{ $kra['avg_score'] >= 70 ? 'bg-green-100 text-green-700' : ($kra['avg_score'] >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ $kra['avg_score'] }}% avg
                </span>
                @else
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-400 font-medium">No logs</span>
                @endif
            </div>
        </div>

        {{-- Sub-KRAs --}}
        @foreach($kra['sub_kras'] as $sub)
        <div class="border-b border-slate-100 last:border-b-0" x-data="{ open: {{ $sub['logs_count'] > 0 ? 'true' : 'false' }} }">

            {{-- Sub-KRA Row --}}
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 px-5 py-3 hover:bg-slate-50 cursor-pointer select-none"
                 @click="open = !open">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-sm font-semibold text-slate-700 truncate">{{ $sub['name'] }}</span>
                </div>

                {{-- Metrics pills --}}
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded font-medium">{{ $sub['weightage'] }}% weight</span>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded font-medium">{{ $sub['review_period'] }}</span>
                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded font-medium">{{ $sub['unit'] }} · {{ $sub['measure_type'] }}</span>
                    <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded font-medium" title="{{ $sub['logic_type'] }}">{{ $sub['logic_name'] }}</span>
                    @if($sub['target_value'] !== '—')
                    <span class="px-2 py-0.5 bg-teal-50 text-teal-700 rounded font-medium">Target: {{ $sub['target_value'] }}</span>
                    @endif
                    <span class="px-2 py-0.5 bg-slate-50 text-slate-500 rounded">{{ $sub['logs_count'] }} log{{ $sub['logs_count'] != 1 ? 's' : '' }}</span>
                    @if($sub['total_hours'] > 0)
                    <span class="px-2 py-0.5 bg-slate-50 text-slate-500 rounded">{{ number_format($sub['total_hours'], 1) }}h</span>
                    @endif
                    @if($sub['avg_score'] > 0)
                    <span class="px-2.5 py-0.5 rounded font-semibold
                        {{ $sub['avg_score'] >= 70 ? 'bg-green-100 text-green-700' : ($sub['avg_score'] >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                        {{ $sub['avg_score'] }}%
                    </span>
                    @endif
                </div>
            </div>

            {{-- Work Logs for this Sub-KRA --}}
            <div x-show="open" x-transition.opacity.duration.200ms style="display:none;">
                @if($sub['logs']->isEmpty())
                <div class="px-10 py-4 text-xs text-slate-400 italic">No work logs in this period for this Sub-KRA.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-t border-slate-100">
                        <thead class="bg-slate-50 text-slate-400 uppercase tracking-wide">
                            <tr>
                                <th class="px-5 py-2 text-left font-medium">Date</th>
                                <th class="px-3 py-2 text-left font-medium">Title</th>
                                <th class="px-3 py-2 text-left font-medium">App / Module</th>
                                <th class="px-3 py-2 text-left font-medium">Priority</th>
                                <th class="px-3 py-2 text-left font-medium">Test</th>
                                <th class="px-3 py-2 text-left font-medium">Dur (T/A)</th>
                                <th class="px-3 py-2 text-left font-medium">Feedback</th>
                                <th class="px-3 py-2 text-left font-medium">Status</th>
                                <th class="px-3 py-2 text-right font-medium">Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($sub['logs'] as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2 text-slate-500 whitespace-nowrap">{{ $log->log_date->format('d M Y') }}</td>
                                <td class="px-3 py-2 max-w-[200px]">
                                    <p class="font-medium text-slate-700 truncate">{{ $log->title }}</p>
                                    @if($log->remark)
                                        <p class="text-slate-400 truncate">{{ Str::limit($log->remark, 40) }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-slate-500">
                                    {{ optional($log->application)->name ?? '—' }}
                                    @if($log->module)<span class="text-slate-400"> / {{ $log->module->name }}</span>@endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($log->priority)
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $log->priority->color_class }}-100 text-{{ $log->priority->color_class }}-700">
                                        {{ $log->priority->name }}
                                    </span>
                                    @else<span class="text-slate-400">—</span>@endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($log->test_status)
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium
                                        {{ $log->test_status === 'Passed' ? 'bg-green-100 text-green-700' : ($log->test_status === 'Failed' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500') }}">
                                        {{ $log->test_status }}
                                    </span>
                                    @else<span class="text-slate-400">—</span>@endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-slate-500">
                                    @if($log->total_duration || $log->actual_duration)
                                        {{ $log->total_duration ?? 0 }}h / {{ $log->actual_duration ?? 0 }}h
                                        @if($log->total_duration > 0 && $log->actual_duration > $log->total_duration * 1.2)
                                            <span class="text-red-400 ml-1">↑</span>
                                        @elseif($log->total_duration > 0 && $log->actual_duration <= $log->total_duration)
                                            <span class="text-green-400 ml-1">✓</span>
                                        @endif
                                    @else<span class="text-slate-400">—</span>@endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($log->feedbacks->isNotEmpty())
                                        @php $avgR = round($log->feedbacks->avg('rating'), 1); @endphp
                                        <span class="text-amber-500 font-semibold">★ {{ $avgR }}</span>
                                        <span class="text-slate-400">/5</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $log->status->color_class }}-100 text-{{ $log->status->color_class }}-700">
                                        {{ $log->status->name }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <span class="font-bold {{ $log->score_calculated >= 70 ? 'text-green-600' : ($log->score_calculated >= 40 ? 'text-amber-600' : 'text-red-500') }}">
                                        {{ $log->score_calculated }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="8" class="px-5 py-2 text-xs text-slate-500 font-medium">
                                    {{ $sub['logs_count'] }} log{{ $sub['logs_count'] != 1 ? 's' : '' }} &nbsp;·&nbsp;
                                    {{ number_format($sub['total_hours'], 1) }}h logged &nbsp;·&nbsp;
                                    {{ $sub['completed'] }} completed
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span class="font-bold text-xs {{ $sub['avg_score'] >= 70 ? 'text-green-600' : ($sub['avg_score'] >= 40 ? 'text-amber-600' : 'text-red-500') }}">
                                        Avg {{ $sub['avg_score'] }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

        </div>
        @endforeach

    </div>
    @endforeach

    </div>{{-- end Tab 2: KRA Matrix --}}

</div>{{-- end x-data --}}

@push('scripts')
<script>
    Chart.defaults.font.family = 'sans-serif';
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#64748b';

    new Chart(document.getElementById('kraScoreChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($kraScores->pluck('name')) !!},
            datasets: [{
                label: 'Score (%)',
                data: {!! json_encode($kraScores->pluck('score')) !!},
                backgroundColor: 'rgba(20,184,166,0.85)',
                borderWidth: 0,
                borderRadius: 6,
                barThickness: 22,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 6 } },
            scales: {
                x: { beginAtZero: true, max: 100, grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { callback: v => v + '%' } },
                y: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    new Chart(document.getElementById('dailyTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyTrend->pluck('date')) !!},
            datasets: [{
                label: 'Tasks',
                data: {!! json_encode($dailyTrend->pluck('count')) !!},
                borderColor: 'rgba(59,130,246,1)',
                backgroundColor: 'rgba(59,130,246,0.08)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(59,130,246,1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 6 } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(148,163,184,0.1)' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
@endsection
