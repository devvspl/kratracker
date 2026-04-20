<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Performia') }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-slate-50 font-sans antialiased text-sm">
    <div class="min-h-screen">
        <!-- Top Navigation -->
        <nav x-data="{ mobileMenuOpen: false }" class="bg-white border-b border-slate-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo and Brand -->
                    <div class="flex items-center space-x-8">
                        <div class="flex items-center space-x-2.5">
                            <div
                                class="w-9 h-9 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-base font-bold text-slate-800">Performia</h1>
                                <p class="text-xs text-slate-500 -mt-0.5">Performance Management</p>
                            </div>
                        </div>

                        <!-- Main Navigation Links -->
                        <div class="hidden md:flex items-center space-x-1">
                            <a href="{{ route('dashboard') }}"
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                    </path>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('work-logs.index') }}"
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('work-logs.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                Work Logs
                            </a>
                            @if (auth()->user()->can_manage_own_kra && !auth()->user()->hasRole('Admin'))
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('my-kra.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        My KRA
                                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                        class="absolute left-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-slate-200 py-1 overflow-hidden">
                                        <p
                                            class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                            My KRA Config</p>
                                        <a href="{{ route('my-kra.kras.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('my-kra.kras.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            KRAs
                                        </a>
                                        <a href="{{ route('my-kra.sub-kras.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('my-kra.sub-kras.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                            </svg>
                                            Sub-KRAs
                                        </a>
                                        <a href="{{ route('my-kra.logics.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('my-kra.logics.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            Logics
                                        </a>
                                        <a href="{{ route('my-kra.task-statuses.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('my-kra.task-statuses.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Task Statuses
                                        </a>
                                        <a href="{{ route('my-kra.priorities.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('my-kra.priorities.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            Priorities
                                        </a>
                                        <a href="{{ route('my-kra.applications.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('my-kra.applications.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            Applications
                                        </a>
                                        <a href="{{ route('my-kra.application-modules.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('my-kra.application-modules.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                            </svg>
                                            Modules
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @role('Admin')
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('masters.kras.*') || request()->routeIs('masters.sub-kras.*') || request()->routeIs('masters.logics.*') || request()->routeIs('masters.task-statuses.*') || request()->routeIs('masters.priorities.*') || request()->routeIs('masters.applications.*') || request()->routeIs('masters.application-modules.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Masters
                                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                        class="absolute left-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-slate-200 py-1 overflow-hidden">
                                        <p
                                            class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                            Masters</p>
                                        <a href="{{ route('masters.kras.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.kras.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            KRAs
                                        </a>
                                        <a href="{{ route('masters.sub-kras.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.sub-kras.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                            </svg>
                                            Sub-KRAs
                                        </a>
                                        <a href="{{ route('masters.logics.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.logics.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            Logics
                                        </a>
                                        <a href="{{ route('masters.task-statuses.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.task-statuses.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Task Statuses
                                        </a>
                                        <a href="{{ route('masters.priorities.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.priorities.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            Priorities
                                        </a>
                                        <a href="{{ route('masters.applications.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.applications.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            Applications
                                        </a>
                                        <a href="{{ route('masters.application-modules.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.application-modules.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                            </svg>
                                            Modules
                                        </a>
                                    </div>
                                </div>

                                {{-- Admin: Users & Reports (Admin only) --}}
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('masters.users.*') || request()->routeIs('reports.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Admin
                                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                        class="absolute left-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-slate-200 py-1 overflow-hidden">
                                        <p
                                            class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                            Admin</p>
                                        <a href="{{ route('masters.users.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.users.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            Users
                                        </a>
                                        <a href="{{ route('reports.index') }}"
                                            class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('reports.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            Reports & Contacts
                                        </a>
                                    </div>
                                </div>
                            @endrole

                            {{-- Reports & Contacts for Manager / can_manage_own_kra users --}}
                            @if (!auth()->user()->hasRole('Admin') && (auth()->user()->hasRole('Manager') || auth()->user()->can_manage_own_kra))
                                <a href="{{ route('reports.index') }}"
                                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('reports.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Reports
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Right Side: Notifications & User -->
                    <div class="flex items-center space-x-3">
                        <!-- Notification Bell -->
                        <div class="relative" x-data="{
                            open: false,
                            unread: 0,
                            items: [],
                            async load() {
                                try {
                                    const r = await fetch('/api/notifications');
                                    const d = await r.json();
                                    this.unread = d.unread;
                                    this.items = d.items;
                                } catch (e) {}
                            },
                            async markRead(id) {
                                await fetch('/api/notifications/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                                this.items = this.items.map(i => i.id === id ? { ...i, is_read: true } : i);
                                this.unread = this.items.filter(i => !i.is_read).length;
                            },
                            async markAll() {
                                await fetch('/api/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                                this.items = this.items.map(i => ({ ...i, is_read: true }));
                                this.unread = 0;
                            },
                            typeIcon(type) {
                                const map = { task_overdue: '⚠️', daily_reminder: '📋', pending_review: '🔔', task_created: '✅', task_completed: '🎉', feedback_added: '💬' };
                                return map[type] || '🔔';
                            },
                            timeAgo(dt) {
                                const s = Math.floor((new Date() - new Date(dt)) / 1000);
                                if (s < 60) return s + 's ago';
                                if (s < 3600) return Math.floor(s / 60) + 'm ago';
                                if (s < 86400) return Math.floor(s / 3600) + 'h ago';
                                return Math.floor(s / 86400) + 'd ago';
                            }
                        }" x-init="load();
                        setInterval(() => load(), 60000)">
                            <button @click="open = !open; if(open) load()"
                                class="relative p-2 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                <span x-show="unread > 0" x-text="unread > 9 ? '9+' : unread"
                                    class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none"
                                    style="display:none;"></span>
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden z-50">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                    <h4 class="text-sm font-bold text-slate-800">Notifications</h4>
                                    <button @click="markAll()" x-show="unread > 0"
                                        class="text-xs text-teal-600 hover:text-teal-700 font-medium">Mark all
                                        read</button>
                                </div>
                                <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                                    <template x-if="items.length === 0">
                                        <div class="px-4 py-8 text-center text-xs text-slate-400">No notifications yet.
                                        </div>
                                    </template>
                                    <template x-for="n in items" :key="n.id">
                                        <div @click="markRead(n.id)" :class="n.is_read ? 'bg-white' : 'bg-teal-50/50'"
                                            class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors">
                                            <span class="text-lg leading-none mt-0.5"
                                                x-text="typeIcon(n.type)"></span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-slate-700 leading-relaxed" x-text="n.message">
                                                </p>
                                                <p class="text-xs text-slate-400 mt-1" x-text="timeAgo(n.created_at)">
                                                </p>
                                            </div>
                                            <span x-show="!n.is_read"
                                                class="w-2 h-2 rounded-full bg-teal-500 shrink-0 mt-1.5"></span>
                                        </div>
                                    </template>
                                </div>
                                <div
                                    class="px-4 py-2.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                                    <a href="{{ route('notifications.all') }}"
                                        class="text-xs text-teal-600 hover:text-teal-700 font-medium">View all
                                        notifications →</a>
                                    <a href="{{ route('dashboard') }}"
                                        class="text-xs text-slate-400 hover:text-slate-600">Dashboard</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Avatar Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="flex items-center space-x-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-all">
                                <div class="text-right hidden md:block">
                                    <p class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ auth()->user()->roles->first()->name ?? 'User' }}</p>
                                </div>
                                <div
                                    class="w-9 h-9 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center">
                                    <span
                                        class="text-white font-semibold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 overflow-hidden">
                                <div class="px-3 py-2 border-b border-slate-100">
                                    <p class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    Profile Settings
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                                </form>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="flex items-center md:hidden ml-1">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                                class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none">
                                <svg x-show="!mobileMenuOpen" class="block h-6 w-6"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <svg x-show="mobileMenuOpen" x-cloak class="block h-6 w-6"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-200 bg-white">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-teal-50 border-teal-500 text-teal-700' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('work-logs.index') }}"
                        class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('work-logs.*') ? 'bg-teal-50 border-teal-500 text-teal-700' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        Work Logs
                    </a>
                    @if (auth()->user()->can_manage_own_kra && !auth()->user()->hasRole('Admin'))
                        <div x-data="{ myKraOpen: {{ request()->routeIs('my-kra.*') ? 'true' : 'false' }} }" class="border-t border-slate-100 mt-2 pt-2">
                            <button @click="myKraOpen = !myKraOpen"
                                class="w-full flex items-center justify-between pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:outline-none">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    My KRA
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200"
                                    :class="{ 'rotate-180': myKraOpen }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="myKraOpen" x-cloak class="bg-slate-50 py-2 space-y-1">
                                <a href="{{ route('my-kra.kras.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('my-kra.kras.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">KRAs</a>
                                <a href="{{ route('my-kra.sub-kras.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('my-kra.sub-kras.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Sub-KRAs</a>
                                <a href="{{ route('my-kra.logics.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('my-kra.logics.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Logics</a>
                                <a href="{{ route('my-kra.task-statuses.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('my-kra.task-statuses.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Task
                                    Statuses</a>
                                <a href="{{ route('my-kra.priorities.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('my-kra.priorities.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Priorities</a>
                                <a href="{{ route('my-kra.applications.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('my-kra.applications.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Applications</a>
                                <a href="{{ route('my-kra.application-modules.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('my-kra.application-modules.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Modules</a>
                            </div>
                        </div>
                    @endif
                    @role('Admin')
                        <div x-data="{ mastersOpen: {{ request()->routeIs('masters.*') ? 'true' : 'false' }} }" class="border-t border-slate-100 mt-2 pt-2">
                            <button @click="mastersOpen = !mastersOpen"
                                class="w-full flex items-center justify-between pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:outline-none">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Masters
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': mastersOpen }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="mastersOpen" x-cloak class="bg-slate-50 py-2 space-y-1">
                                <a href="{{ route('masters.kras.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.kras.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">KRAs</a>
                                <a href="{{ route('masters.sub-kras.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.sub-kras.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Sub-KRAs</a>
                                <a href="{{ route('masters.logics.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.logics.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Logics</a>
                                <a href="{{ route('masters.task-statuses.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.task-statuses.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Task
                                    Statuses</a>
                                <a href="{{ route('masters.priorities.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.priorities.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Priorities</a>
                                <a href="{{ route('masters.applications.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.applications.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Applications</a>
                                <a href="{{ route('masters.application-modules.index') }}"
                                    class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.application-modules.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Modules</a>
                            </div>
                        </div>
                        <a href="{{ route('masters.users.index') }}"
                            class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('masters.users.*') ? 'bg-teal-50 border-teal-500 text-teal-700' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Users
                        </a>
                        <a href="{{ route('reports.index') }}"
                            class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('reports.*') ? 'bg-teal-50 border-teal-500 text-teal-700' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Reports & Contacts
                        </a>
                    @endrole
                    @if (!auth()->user()->hasRole('Admin') && (auth()->user()->hasRole('Manager') || auth()->user()->can_manage_own_kra))
                        <a href="{{ route('reports.index') }}"
                            class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('reports.*') ? 'bg-teal-50 border-teal-500 text-teal-700' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Reports
                        </a>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Toast notification function
        window.showToast = function(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 text-sm ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white font-medium`;
            toast.textContent = message;

            document.getElementById('toast-container').appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };
    </script>

    @stack('scripts')

    {{-- Browser Push Notifications --}}
    <script>
        (function() {
            // Only run for authenticated users
            if (!('Notification' in window)) return;

            const CSRF = document.querySelector('meta[name=csrf-token]')?.content;
            let lastSeenId = parseInt(localStorage.getItem('kra_last_notif_id') || '0');
            let permissionGranted = Notification.permission === 'granted';

            // Request permission on first load
            function requestPermission() {
                if (Notification.permission === 'default') {
                    Notification.requestPermission().then(p => {
                        permissionGranted = p === 'granted';
                    });
                }
            }

            // Show a browser push notification
            function pushNotify(title, body, icon) {
                if (!permissionGranted) return;
                try {
                    const n = new Notification(title, {
                        body: body,
                        icon: icon || '/favicon.ico',
                        badge: '/favicon.ico',
                        tag: 'kra-tracker',
                        renotify: true,
                    });
                    n.onclick = function() {
                        window.focus();
                        window.location.href = '/notifications';
                        n.close();
                    };
                    setTimeout(() => n.close(), 8000);
                } catch (e) {}
            }

            // Poll for new notifications every 30 seconds
            async function poll() {
                try {
                    const r = await fetch('/api/notifications', {
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        }
                    });
                    if (!r.ok) return;
                    const data = await r.json();

                    // Find notifications newer than last seen
                    const newItems = (data.items || []).filter(n =>
                        !n.is_read && n.id > lastSeenId
                    );

                    if (newItems.length > 0) {
                        // Update last seen
                        const maxId = Math.max(...newItems.map(n => n.id));
                        lastSeenId = maxId;
                        localStorage.setItem('kra_last_notif_id', maxId);

                        // Show one push per new notification (max 3 to avoid spam)
                        const toShow = newItems.slice(0, 3);
                        const typeLabels = {
                            task_overdue: '⚠️ Overdue Task',
                            daily_reminder: '📋 Daily Reminder',
                            pending_review: '🔔 Pending Review',
                            task_created: '✅ Task Created',
                            task_completed: '🎉 Task Completed',
                            feedback_added: '💬 New Feedback',
                        };

                        toShow.forEach((n, i) => {
                            setTimeout(() => {
                                const title = typeLabels[n.type] || '🔔 Performia';
                                pushNotify(title, n.message);
                            }, i * 1500); // stagger by 1.5s each
                        });
                    }
                } catch (e) {}
            }

            // Kick off
            requestPermission();

            // Wait a moment then start polling
            setTimeout(() => {
                poll();
                setInterval(poll, 30000); // every 30 seconds
            }, 3000);
        })();
    </script>
</body>

</html>
