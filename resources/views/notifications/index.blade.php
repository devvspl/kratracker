@extends('layouts.app')
@section('content')

@php
$typeConfig = [
    'task_overdue'   => ['icon' => '⚠️', 'label' => 'Overdue',        'accent' => 'bg-red-500',    'badge' => 'bg-red-100 text-red-700'],
    'daily_reminder' => ['icon' => '📋', 'label' => 'Daily Reminder', 'accent' => 'bg-blue-500',   'badge' => 'bg-blue-100 text-blue-700'],
    'pending_review' => ['icon' => '🔔', 'label' => 'Pending Review', 'accent' => 'bg-amber-500',  'badge' => 'bg-amber-100 text-amber-700'],
    'task_created'   => ['icon' => '✅', 'label' => 'Created',        'accent' => 'bg-teal-500',   'badge' => 'bg-teal-100 text-teal-700'],
    'task_completed' => ['icon' => '🎉', 'label' => 'Completed',      'accent' => 'bg-green-500',  'badge' => 'bg-green-100 text-green-700'],
    'feedback_added' => ['icon' => '💬', 'label' => 'Feedback',       'accent' => 'bg-purple-500', 'badge' => 'bg-purple-100 text-purple-700'],
];
$default = ['icon' => '🔔', 'label' => 'Notification', 'accent' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-600'];
@endphp

<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
        <div>
            <h2 class="text-base font-bold text-slate-800">Notifications</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                @if($unreadCount > 0)
                    <span class="text-teal-600 font-medium">{{ $unreadCount }} unread</span> notification{{ $unreadCount > 1 ? 's' : '' }}
                @else
                    You're all caught up!
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex gap-1 bg-white border border-slate-200 rounded-lg p-1 shadow-sm">
                <a href="{{ route('notifications.all', ['filter' => 'all']) }}"
                   class="px-4 py-1.5 text-xs font-semibold rounded-md transition-all {{ $filter === 'all' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                    All
                </a>
                <a href="{{ route('notifications.all', ['filter' => 'unread']) }}"
                   class="px-4 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5 {{ $filter === 'unread' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                    Unread
                    @if($unreadCount > 0)
                    <span class="px-1.5 py-0.5 {{ $filter === 'unread' ? 'bg-white/30 text-white' : 'bg-red-500 text-white' }} text-[10px] font-bold rounded-full leading-none">{{ $unreadCount }}</span>
                    @endif
                </a>
            </div>
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="px-3 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark all read
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('status'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('status') }}
    </div>
    @endif

    {{-- Notification cards --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($notifications as $n)
        @php $cfg = $typeConfig[$n->type] ?? $default; @endphp

        <div class="flex items-center gap-3 px-4 py-3 {{ $n->is_read ? '' : 'bg-teal-50/40' }} hover:bg-slate-50 transition-colors">

            {{-- Accent dot --}}
            <span class="w-2 h-2 rounded-full shrink-0 {{ $n->is_read ? 'bg-slate-200' : $cfg['accent'] }}"></span>

            {{-- Icon --}}
            <span class="text-base leading-none shrink-0">{{ $cfg['icon'] }}</span>

            {{-- Type badge --}}
            <span class="px-2 py-0.5 text-xs font-semibold rounded-full shrink-0 {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>

            {{-- Message --}}
            <p class="text-xs text-slate-700 flex-1 truncate">{{ $n->message }}</p>

            {{-- Task title chip --}}
            @if(isset($n->data['title']))
            <span class="hidden sm:flex items-center gap-1 text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full shrink-0 max-w-[140px] truncate">
                <svg class="w-3 h-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="truncate">{{ $n->data['title'] }}</span>
            </span>
            @endif

            {{-- Time --}}
            <span class="text-xs text-slate-400 shrink-0 whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>

            {{-- View link --}}
            @if(isset($n->data['work_log_id']))
            <a href="{{ route('work-logs.index', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}"
               class="shrink-0 flex items-center gap-1 text-xs text-teal-600 hover:text-teal-700 font-medium bg-teal-50 hover:bg-teal-100 px-2.5 py-1 rounded-lg transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                View
            </a>
            @else
            <span class="w-[58px] shrink-0"></span>
            @endif

        </div>

        @empty
        <div class="p-14 text-center">
            <div class="text-5xl mb-4">🔔</div>
            <p class="text-sm font-semibold text-slate-600 mb-1">No notifications</p>
            <p class="text-xs text-slate-400">
                {{ $filter === 'unread' ? "You're all caught up." : 'Notifications will appear here when tasks need attention.' }}
            </p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-4">{{ $notifications->links() }}</div>
    @endif

</div>
@endsection
