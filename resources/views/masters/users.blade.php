@extends('layouts.app')
@section('content')

@php $json = json_encode($users->map(fn($u) => [
    'id'                 => $u->id,
    'name'               => $u->name,
    'email'              => $u->email,
    'role'               => $u->roles->first()?->name ?? '',
    'can_manage_own_kra' => (bool) $u->can_manage_own_kra,
])->toArray()); @endphp

<div x-data="{
    items: {{ $json }},
    showModal: false, showDelete: false, mode: 'create', loading: false, deleteId: null,
    form: { name:'', email:'', password:'', role:'Employee', can_manage_own_kra: false },

    openCreate() {
        this.mode = 'create';
        this.form = { name:'', email:'', password:'', role:'Employee', can_manage_own_kra: false };
        this.showModal = true;
    },
    openEdit(id) {
        this.mode = 'edit';
        const item = this.items.find(i => i.id === id);
        if (!item) return;
        this.form = { name: item.name, email: item.email, password: '', role: item.role, can_manage_own_kra: item.can_manage_own_kra, _id: id };
        this.showModal = true;
    },
    confirmDelete(id) { this.deleteId = id; this.showDelete = true; },
    async submit() {
        this.loading = true;
        const isEdit = this.mode === 'edit';
        const url = isEdit ? '/masters/users/' + this.form._id : '/masters/users';
        const body = { name: this.form.name, email: this.form.email, role: this.form.role, can_manage_own_kra: this.form.can_manage_own_kra };
        if (this.form.password) body.password = this.form.password;
        try {
            const res = await fetch(url, { method: isEdit ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify(body) });
            const data = await res.json();
            if (res.status === 422) { return window.showToast(Object.values(data.errors).flat().join(' '), 'error'); }
            if (data.success) { window.showToast(data.message, 'success'); this.showModal = false; setTimeout(() => location.reload(), 800); }
            else { window.showToast(data.message || 'Error', 'error'); }
        } catch(e) { window.showToast('Network error', 'error'); }
        finally { this.loading = false; }
    },
    async deleteItem() {
        this.loading = true;
        try {
            const res = await fetch('/masters/users/' + this.deleteId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
            const data = await res.json();
            if (data.success) { window.showToast(data.message, 'success'); this.showDelete = false; setTimeout(() => location.reload(), 800); }
            else { window.showToast(data.message || 'Error', 'error'); }
        } catch(e) { window.showToast('Network error', 'error'); }
        finally { this.loading = false; }
    }
}">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-base font-bold text-slate-800">User Management</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage system users and their roles</p>
        </div>
        <button @click="openCreate()" class="px-3 py-2 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 flex items-center gap-1.5 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </button>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">KRA Config</th>
                    <th class="px-4 py-3 text-left">Joined</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $i => $user)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center shrink-0">
                                <span class="text-white font-semibold text-xs">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <span class="font-medium text-slate-800">{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                                <span class="px-1.5 py-0.5 text-xs bg-teal-50 text-teal-600 rounded font-medium">You</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @php $role = $user->roles->first(); @endphp
                        @if($role)
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium
                            {{ $role->name === 'Admin' ? 'bg-red-100 text-red-700' : ($role->name === 'Manager' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ $role->name }}
                        </span>
                        @else
                        <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($user->can_manage_own_kra)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full font-medium bg-teal-100 text-teal-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Self-Manage
                            </span>
                        @else
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-slate-100 text-slate-500">Admin Only</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <button @click="openEdit({{ $user->id }})" title="Edit" class="p-1 text-slate-400 hover:text-blue-600 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if($user->id !== auth()->id())
                            <button @click="confirmDelete({{ $user->id }})" title="Delete" class="p-1 text-slate-400 hover:text-red-600 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create / Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div @click="showModal = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800" x-text="mode === 'create' ? 'Add User' : 'Edit User'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submit()" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" required placeholder="e.g. John Doe"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" x-model="form.email" required placeholder="user@example.com"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Password <span x-show="mode === 'create'" class="text-red-500">*</span>
                        <span x-show="mode === 'edit'" class="text-slate-400 font-normal">(leave blank to keep current)</span>
                    </label>
                    <input type="password" x-model="form.password" :required="mode === 'create'" placeholder="Min. 8 characters"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select x-model="form.role" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- KRA Self-Management --}}
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.can_manage_own_kra"
                            class="w-4 h-4 mt-0.5 text-teal-600 border-slate-300 rounded focus:ring-teal-500 shrink-0">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Allow Self-Manage KRA Config</p>
                            <p class="text-xs text-slate-500 mt-0.5">User can configure their own Sub-KRAs, scoring logic, and period targets. When disabled, only Admins can manage their KRA setup.</p>
                        </div>
                    </label>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" :disabled="loading" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="loading ? 'Saving...' : 'Save'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div @click="showDelete = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center" @click.stop>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Delete User?</h3>
            <p class="text-sm text-slate-500 mb-6">This will permanently remove the user and all their data.</p>
            <div class="flex gap-3 justify-center">
                <button @click="showDelete = false" class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                <button @click="deleteItem()" :disabled="loading" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                    <span x-text="loading ? 'Deleting...' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
