@extends('layouts.app')
@section('content')

@php $json = json_encode($modules->load('application')->toArray()); @endphp

<div x-data="{
    items: {{ $json }},
    showModal: false, showDelete: false, mode: 'create', loading: false, deleteId: null,
    form: { name:'', application_id:'', is_active:true },

    openCreate() { this.mode='create'; this.form={ name:'', application_id:'', is_active:true }; this.showModal=true; },
    openEdit(id) {
        this.mode='edit';
        const item=this.items.find(i=>i.id===id);
        if(!item) return;
        this.form={ name:item.name, application_id:item.application_id||'', is_active:item.is_active, _id:id };
        this.showModal=true;
    },
    confirmDelete(id) { this.deleteId=id; this.showDelete=true; },
    async submit() {
        this.loading=true;
        const isEdit=this.mode==='edit';
        const url=isEdit?'{{ $baseUrl }}/'+this.form._id:'{{ $baseUrl }}';
        const body={ name:this.form.name, application_id:this.form.application_id||null, is_active:this.form.is_active };
        try {
            const res=await fetch(url,{method:isEdit?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(body)});
            const data=await res.json();
            if(data.success){window.showToast(data.message,'success');this.showModal=false;setTimeout(()=>location.reload(),800);}
            else{window.showToast(data.message||'Error','error');}
        }catch(e){window.showToast('Network error','error');}
        finally{this.loading=false;}
    },
    async deleteItem() {
        this.loading=true;
        try {
            const res=await fetch('{{ $baseUrl }}/'+this.deleteId,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}});
            const data=await res.json();
            if(data.success){window.showToast(data.message,'success');this.showDelete=false;setTimeout(()=>location.reload(),800);}
            else{window.showToast(data.message||'Error','error');}
        }catch(e){window.showToast('Network error','error');}
        finally{this.loading=false;}
    }
}">
    <div class="flex items-center justify-between mb-5">
        <div><h2 class="text-base font-bold text-slate-800">Module Master</h2><p class="text-xs text-slate-500 mt-0.5">Manage application modules</p></div>
        <button @click="openCreate()" class="px-3 py-2 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Module
        </button>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Module Name</th>
                    <th class="px-4 py-3 text-left">Application</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($modules as $i => $mod)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-400">{{ $i+1 }}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $mod->name }}</td>
                    <td class="px-4 py-3">
                        @if($mod->application)
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">{{ $mod->application->name }}</span>
                        @else
                            <span class="text-xs text-slate-400">Global</span>
                        @endif
                    </td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $mod->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $mod->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button @click="openEdit({{ $mod->id }})" class="p-1 text-slate-400 hover:text-blue-600 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button @click="confirmDelete({{ $mod->id }})" class="p-1 text-slate-400 hover:text-red-600 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No modules found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div @click="showModal=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800" x-text="mode==='create' ? 'Add Module' : 'Edit Module'"></h3>
                <button @click="showModal=false" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form @submit.prevent="submit()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Module Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none" placeholder="e.g. User Management">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Application <span class="text-xs text-slate-400">(leave blank for global)</span></label>
                        <select x-model="form.application_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                            <option value="">Global (all applications)</option>
                            @foreach($applications as $app)
                                <option value="{{ $app->id }}">{{ $app->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="w-4 h-4 text-teal-600 border-slate-300 rounded">
                        <span class="text-sm text-slate-700">Active</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showModal=false" class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" :disabled="loading" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="loading ? 'Saving...' : 'Save'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div @click="showDelete=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center" @click.stop>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Delete Module?</h3>
            <p class="text-sm text-slate-500 mb-6">This action cannot be undone.</p>
            <div class="flex gap-3 justify-center">
                <button @click="showDelete=false" class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                <button @click="deleteItem()" :disabled="loading" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection
