@extends('layouts.app')
@section('content')

<div x-data="{ tab: 'info' }">

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

                        {{-- Read-only info --}}
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

            {{-- Tab: Delete Account --}}
            <div x-show="tab === 'danger'" x-transition.opacity.duration.150ms style="display:none;" x-data="{ showConfirm: false }">
                <div class="bg-white rounded-xl border border-red-200 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-red-700 mb-1">Delete Account</h3>
                    <p class="text-xs text-slate-500 mb-5">Once deleted, all your data — work logs, feedback, and settings — will be permanently removed. This cannot be undone.</p>

                    <button @click="showConfirm = true" class="px-5 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                        Delete My Account
                    </button>

                    {{-- Confirm panel --}}
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
