<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Performia') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans antialiased bg-slate-50 text-sm">

    <div class="min-h-screen flex">

        {{-- Left Panel --}}
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-teal-600 to-teal-800 flex-col justify-between p-12 relative overflow-hidden">
            {{-- Background decoration --}}
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full"></div>
                <div class="absolute top-1/3 -left-16 w-64 h-64 bg-white/5 rounded-full"></div>
                <div class="absolute -bottom-16 right-1/4 w-80 h-80 bg-white/5 rounded-full"></div>
            </div>

            {{-- Brand --}}
            <div class="relative flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-white font-bold text-lg leading-tight">Performia</h1>
                    <p class="text-teal-200 text-xs">Performance Management</p>
                </div>
            </div>

            {{-- Hero content --}}
            <div class="relative">
                <h2 class="text-white text-3xl font-bold leading-snug mb-4">
                    Track. Measure.<br>Improve.
                </h2>
                <p class="text-teal-100 text-sm leading-relaxed max-w-sm">
                    Manage your Key Result Areas, log daily work, and get clear visibility into your performance — all in one place.
                </p>

                {{-- Feature pills --}}
                <div class="mt-8 flex flex-wrap gap-2">
                    <span class="px-3 py-1.5 bg-white/10 text-white text-xs rounded-full backdrop-blur-sm">Daily Work Logs</span>
                    <span class="px-3 py-1.5 bg-white/10 text-white text-xs rounded-full backdrop-blur-sm">KRA Scoring</span>
                    <span class="px-3 py-1.5 bg-white/10 text-white text-xs rounded-full backdrop-blur-sm">Analytics</span>
                    <span class="px-3 py-1.5 bg-white/10 text-white text-xs rounded-full backdrop-blur-sm">Feedback</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="relative">
                <p class="text-teal-300 text-xs">&copy; {{ date('Y') }} Performia. All rights reserved.</p>
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">

                {{-- Mobile brand --}}
                <div class="flex items-center space-x-2.5 mb-8 lg:hidden">
                    <div class="w-9 h-9 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-800">Performia</h1>
                        <p class="text-xs text-slate-500 -mt-0.5">Performance Management</p>
                    </div>
                </div>

                {{ $slot }}

            </div>
        </div>

    </div>

</body>
</html>
