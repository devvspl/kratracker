@extends('layouts.app')
@section('content')

<div x-data="{ tab: '{{ request('tab', 'info') }}' }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-base font-bold text-slate-800">Profile Settings</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage your account information and security</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

        {{-- Left: Avatar card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center mx-auto mb-3">
                    <span class="text-white font-bold text-3xl">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <p class="font-bold text-slate-800 text-sm">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ auth()->user()->email }}</p>
                <span class="inline-block mt-2 px-2.5 py-0.5 text-xs rounded-full font-medium
                    {{ auth()->user()->roles->first()?->name === 'Admin' ? 'bg-red-100 text-red-700' : (auth()->user()->roles->first()?->name === 'Manager' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                    {{ auth()->user()->roles->first()?->name ?? 'User' }}
                </span>
                <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-400 space-y-1">
                    <p>Member since</p>
                    <p class="font-medium text-slate-600">{{ auth()->user()->created_at->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Tab nav --}}
            <div class="mt-3 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <button @click="tab = 'info'" :class="tab === 'info' ? 'bg-teal-50 text-teal-700 border-l-2 border-teal-500' : 'text-slate-600 hover:bg-slate-50 border-l-2 border-transparent'"
                    class="w-full flex items-center gap-2.5 px-4 py-3 text-xs font-medium transition-colors text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile Information
                </button>
                <button @click="tab = 'password'" :class="tab === 'password' ? 'bg-teal-50 text-teal-700 border-l-2 border-teal-500' : 'text-slate-600 hover:bg-slate-50 border-l-2 border-transparent'"
                    class="w-full flex items-center gap-2.5 px-4 py-3 text-xs font-medium transition-colors text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Change Password
                </button>
                @if(auth()->user()->roles->first()?->name === 'Admin')
                <button @click="tab = 'backup'" :class="tab === 'backup' ? 'bg-teal-50 text-teal-700 border-l-2 border-teal-500' : 'text-slate-600 hover:bg-slate-50 border-l-2 border-transparent'"
                    class="w-full flex items-center gap-2.5 px-4 py-3 text-xs font-medium transition-colors text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16M12 11v6m0 0l-2-2m2 2l2-2"/></svg>
                    Database Backups
                </button>
                @endif
                <button @click="tab = 'danger'" :class="tab === 'danger' ? 'bg-red-50 text-red-700 border-l-2 border-red-500' : 'text-slate-600 hover:bg-slate-50 border-l-2 border-transparent'"
                    class="w-full flex items-center gap-2.5 px-4 py-3 text-xs font-medium transition-colors text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Account
                </button>
            </div>
        </div>

        {{-- Right: Tab panels --}}
        <div class="lg:col-span-3">

            {{-- Tab: Profile Info --}}
            <div x-show="tab === 'info'" x-transition.opacity.duration.150ms>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Profile Information</h3>
                    <p class="text-xs text-slate-500 mb-5">Update your name and email address.</p>

                    @if(session('status') === 'profile-updated')
                    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Profile updated successfully.
                    </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-xs font-medium text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
                                    class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->get('name') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100' }}">
                                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-medium text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->get('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100' }}">
                                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Role</label>
                                <div class="px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-600">
                                    {{ auth()->user()->roles->first()?->name ?? '—' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Member Since</label>
                                <div class="px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-600">
                                    {{ auth()->user()->created_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Change Password --}}
            <div x-show="tab === 'password'" x-transition.opacity.duration.150ms style="display:none;">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Change Password</h3>
                    <p class="text-xs text-slate-500 mb-5">Use a strong password of at least 8 characters.</p>

                    @if(session('status') === 'password-updated')
                    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Password updated successfully.
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="block text-xs font-medium text-slate-700 mb-1.5">Current Password <span class="text-red-500">*</span></label>
                            <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                                class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->updatePassword->get('current_password') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100' }}">
                            @if($errors->updatePassword->get('current_password'))
                                <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-medium text-slate-700 mb-1.5">New Password <span class="text-red-500">*</span></label>
                                <input id="password" type="password" name="password" autocomplete="new-password"
                                    class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->updatePassword->get('password') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100' }}">
                                @if($errors->updatePassword->get('password'))
                                    <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('password') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-xs font-medium text-slate-700 mb-1.5">Confirm New Password <span class="text-red-500">*</span></label>
                                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                                    class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-all border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors shadow-sm">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Database Backups (Admin only) --}}
            @if(auth()->user()->roles->first()?->name === 'Admin')
            <div x-show="tab === 'backup'" x-transition.opacity.duration.150ms style="display:none;">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">

                    {{-- Header row --}}
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Database Backups</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Automatic daily backup at midnight. Keeps last 30 backups.</p>
                        </div>
                        <form method="POST" action="{{ route('backup.create') }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 text-white text-xs font-semibold rounded-lg hover:bg-teal-700 transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Backup Now
                            </button>
                        </form>
                    </div>

                    {{-- Flash messages --}}
                    @if(session('backup_status'))
                    <div class="mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ session('backup_status') }}
                    </div>
                    @endif
                    @if(session('backup_error'))
                    <div class="mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('backup_error') }}
                    </div>
                    @endif

                    {{-- Schedule info banner --}}
                    <div class="mt-4 flex items-center gap-3 px-4 py-3 bg-teal-50 border border-teal-100 rounded-lg">
                        <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-teal-700">
                            <span class="font-semibold">Auto-schedule active:</span>
                            Daily at <span class="font-mono font-semibold">00:00</span> &mdash; backups stored in
                            <span class="font-mono font-semibold">storage/app/backups/</span>
                        </p>
                    </div>

                    {{-- Backup table --}}
                    <div class="mt-5">
                        @if(isset($backups) && count($backups) > 0)
                        <div class="overflow-hidden rounded-lg border border-slate-200">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Filename</th>
                                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Created</th>
                                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Size</th>
                                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($backups as $index => $backup)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="font-mono text-slate-700 truncate max-w-[220px]" title="{{ $backup['name'] }}">
                                                    {{ $backup['name'] }}
                                                </span>
                                                @if($index === 0)
                                                    <span class="px-1.5 py-0.5 bg-teal-100 text-teal-700 text-[10px] font-semibold rounded">Latest</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">{{ $backup['created'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-500">{{ $backup['size'] }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('backup.download', $backup['name']) }}"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-teal-50 text-teal-700 border border-teal-200 rounded-md hover:bg-teal-100 transition-colors font-medium">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    Download
                                                </a>
                                                <form method="POST" action="{{ route('backup.destroy', $backup['name']) }}"
                                                    onsubmit="return confirm('Delete this backup file permanently?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-md hover:bg-red-100 transition-colors font-medium">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <p class="text-xs font-medium text-slate-500">No backups found</p>
                            <p class="text-xs text-slate-400 mt-1">Click "Backup Now" to create your first backup.</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
            @endif

            {{-- Tab: Delete Account --}}
            <div x-show="tab === 'danger'" x-transition.opacity.duration.150ms style="display:none;" x-data="{ showConfirm: false }">
                <div class="bg-white rounded-xl border border-red-200 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-red-700 mb-1">Delete Account</h3>
                    <p class="text-xs text-slate-500 mb-5">Once deleted, all your data — work logs, feedback, and settings — will be permanently removed. This cannot be undone.</p>

                    <button @click="showConfirm = true" class="px-5 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                        Delete My Account
                    </button>

                    <div x-show="showConfirm" x-transition class="mt-5 p-4 bg-red-50 border border-red-200 rounded-lg" style="display:none;">
                        <p class="text-xs font-semibold text-red-700 mb-3">Enter your password to confirm account deletion:</p>
                        <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-3">
                            @csrf
                            @method('DELETE')
                            <input type="password" name="password" required placeholder="Your current password"
                                class="w-full md:w-72 px-3 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->userDeletion->get('password') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100' }}">
                            @if($errors->userDeletion->get('password'))
                                <p class="text-xs text-red-600">{{ $errors->userDeletion->first('password') }}</p>
                            @endif
                            <div class="flex gap-3">
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Yes, Delete Permanently
                                </button>
                                <button type="button" @click="showConfirm = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

@if($errors->updatePassword->any())
<script>document.addEventListener('DOMContentLoaded', () => { document.querySelector('[x-data]').__x.$data.tab = 'password'; });</script>
@endif
@if($errors->userDeletion->any())
<script>document.addEventListener('DOMContentLoaded', () => { document.querySelector('[x-data]').__x.$data.tab = 'danger'; });</script>
@endif

@endsection