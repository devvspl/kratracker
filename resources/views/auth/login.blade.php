<x-guest-layout>

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800">Welcome back</h2>
        <p class="text-slate-500 text-sm mt-1">Sign in to your Performia account</p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-xs font-medium text-slate-700 mb-1.5">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
                class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-all placeholder:text-slate-400
                       {{ $errors->get('email') ? 'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200' : 'border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100' }}"
            >
            @error('email')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-medium text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-all placeholder:text-slate-400
                       {{ $errors->get('password') ? 'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200' : 'border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100' }}"
            >
            @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me (always enabled — hidden) --}}
        <input type="hidden" name="remember" value="1">

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
        >
            Sign In
        </button>

    </form>

</x-guest-layout>
