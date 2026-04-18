<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KRA Tracker') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        [x-cloak] { display: none !important; }
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
                            <div class="w-9 h-9 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-base font-bold text-slate-800">KRA Tracker</h1>
                                <p class="text-xs text-slate-500 -mt-0.5">Performance Management</p>
                            </div>
                        </div>

                        <!-- Main Navigation Links -->
                        <div class="hidden md:flex items-center space-x-1">
                            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('work-logs.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('work-logs.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Work Logs
                            </a>
                            @role('Admin')
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('masters.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Masters
                                    <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-slate-200 py-1 overflow-hidden">
                                    <p class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Masters</p>
                                    <a href="{{ route('masters.kras.index') }}" class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.kras.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        KRAs
                                    </a>
                                    <a href="{{ route('masters.sub-kras.index') }}" class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.sub-kras.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                        Sub-KRAs
                                    </a>
                                    <a href="{{ route('masters.logics.index') }}" class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.logics.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        Logics
                                    </a>
                                    <a href="{{ route('masters.task-statuses.index') }}" class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.task-statuses.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Task Statuses
                                    </a>
                                    <a href="{{ route('masters.priorities.index') }}" class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.priorities.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        Priorities
                                    </a>
                                    <a href="{{ route('masters.applications.index') }}" class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ request()->routeIs('masters.applications.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        Applications
                                    </a>
                                </div>
                            </div>
                            @endrole
                        </div>
                    </div>

                    <!-- Right Side: Notifications & User -->
                    <div class="flex items-center space-x-3">
                        <!-- Notification Bell -->
                        <button class="relative p-2 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        </button>
                        
                        <!-- User Avatar Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-all">
                                <div class="text-right hidden md:block">
                                    <p class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth()->user()->roles->first()->name ?? 'User' }}</p>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 overflow-hidden">
                                <div class="px-3 py-2 border-b border-slate-100">
                                    <p class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Profile Settings
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                                </form>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="flex items-center md:hidden ml-1">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none">
                                <svg x-show="!mobileMenuOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <svg x-show="mobileMenuOpen" x-cloak class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-200 bg-white">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-teal-50 border-teal-500 text-teal-700' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('work-logs.index') }}" class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('work-logs.*') ? 'bg-teal-50 border-teal-500 text-teal-700' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Work Logs
                    </a>
                    @role('Admin')
                    <div x-data="{ mastersOpen: {{ request()->routeIs('masters.*') ? 'true' : 'false' }} }" class="border-t border-slate-100 mt-2 pt-2">
                        <button @click="mastersOpen = !mastersOpen" class="w-full flex items-center justify-between pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:outline-none">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Masters
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': mastersOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="mastersOpen" x-cloak class="bg-slate-50 py-2 space-y-1">
                            <a href="{{ route('masters.kras.index') }}" class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.kras.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">KRAs</a>
                            <a href="{{ route('masters.sub-kras.index') }}" class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.sub-kras.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Sub-KRAs</a>
                            <a href="{{ route('masters.logics.index') }}" class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.logics.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Logics</a>
                            <a href="{{ route('masters.task-statuses.index') }}" class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.task-statuses.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Task Statuses</a>
                            <a href="{{ route('masters.priorities.index') }}" class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.priorities.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Priorities</a>
                            <a href="{{ route('masters.applications.index') }}" class="block pl-10 pr-4 py-2 text-sm font-medium {{ request()->routeIs('masters.applications.*') ? 'text-teal-700 bg-teal-50 border-l-2 border-teal-500' : 'text-slate-600 hover:text-slate-900 border-l-2 border-transparent' }}">Applications</a>
                        </div>
                    </div>
                    @endrole
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
</body>
</html>
