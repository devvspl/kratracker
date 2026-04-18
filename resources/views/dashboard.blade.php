@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Overall KRA Score Card -->
        <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-6 shadow-xl shadow-teal-500/30 text-white transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-lg rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-teal-100 font-medium mb-1">Overall KRA Score</p>
            <p class="text-4xl font-bold">{{ number_format($overallScore, 1) }}%</p>
            <div class="mt-4 flex items-center text-sm text-teal-100">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Current Period
            </div>
        </div>

        <!-- Tasks This Month Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-slate-500 font-medium mb-1">Tasks This Month</p>
            <p class="text-4xl font-bold text-slate-800">{{ $tasksLoggedThisMonth }}</p>
            <div class="mt-4 flex items-center text-sm text-slate-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Total logged
            </div>
        </div>

        <!-- Completed Tasks Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-slate-500 font-medium mb-1">Completed</p>
            <p class="text-4xl font-bold text-slate-800">{{ $tasksCompletedThisMonth }}</p>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Successfully done
            </div>
        </div>

        <!-- Pending Tasks Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-slate-500 font-medium mb-1">Pending Tasks</p>
            <p class="text-4xl font-bold text-slate-800">{{ $pendingTasks }}</p>
            <div class="mt-4 flex items-center text-sm text-amber-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                In progress
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- KRA-wise Score Chart -->
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">KRA-wise Performance</h3>
                    <p class="text-sm text-slate-500 mt-1">Score distribution across KRAs</p>
                </div>
                <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <canvas id="kraScoreChart" class="max-h-80"></canvas>
        </div>

        <!-- Daily Trend Chart -->
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Daily Work Log Trend</h3>
                    <p class="text-sm text-slate-500 mt-1">Last 30 days activity</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
            </div>
            <canvas id="dailyTrendChart" class="max-h-80"></canvas>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Export Reports</h3>
                <p class="text-sm text-slate-500 mt-1">Download your performance data</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('export.work-logs') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl hover:from-teal-600 hover:to-teal-700 shadow-lg shadow-teal-500/30 transition-all duration-200 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Work Logs (Excel)
            </a>
            <a href="{{ route('export.kra-summary') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 shadow-lg shadow-blue-500/30 transition-all duration-200 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export KRA Summary (Excel)
            </a>
            <a href="{{ route('export.analytics-pdf') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 shadow-lg shadow-red-500/30 transition-all duration-200 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Export Analytics (PDF)
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Chart.js default configuration
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';
    
    // KRA Score Chart
    const kraCtx = document.getElementById('kraScoreChart').getContext('2d');
    new Chart(kraCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($kraScores->pluck('name')) !!},
            datasets: [{
                label: 'Score (%)',
                data: {!! json_encode($kraScores->pluck('score')) !!},
                backgroundColor: 'rgba(20, 184, 166, 0.8)',
                borderColor: 'rgba(20, 184, 166, 1)',
                borderWidth: 2,
                borderRadius: 12,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    borderRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Daily Trend Chart
    const trendCtx = document.getElementById('dailyTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyTrend->pluck('date')) !!},
            datasets: [{
                label: 'Tasks Logged',
                data: {!! json_encode($dailyTrend->pluck('count')) !!},
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    borderRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
