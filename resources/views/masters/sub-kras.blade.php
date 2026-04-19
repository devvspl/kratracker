@extends('layouts.app')
@section('content')
@php $subKrasJson = json_encode($subKras->toArray()); @endphp
<div x-data="{
    items: {{ $subKrasJson }},
    showModal:false, showDelete:false, mode:'create', loading:false, deleteId:null,
    form:{ kra_id:'', name:'', weightage:0, unit:'%', measure_type:'', logic_id:'', review_period:'Monthly', description:'', is_active:true },
    openCreate(){ this.mode='create'; this.form={ kra_id:'', name:'', weightage:0, unit:'%', measure_type:'', logic_id:'', review_period:'Monthly', description:'', is_active:true }; this.showModal=true; },
    openEdit(id){
        this.mode='edit';
        const item=this.items.find(i=>i.id===id); if(!item) return;
        this.form={ kra_id:item.kra_id, name:item.name, weightage:item.weightage, unit:item.unit, measure_type:item.measure_type||'', logic_id:item.logic_id, review_period:item.review_period, description:item.description||'', is_active:item.is_active, _id:id };
        this.showModal=true;
    },
    confirmDelete(id){ this.deleteId=id; this.showDelete=true; },
    async submit(){
        this.loading=true;
        const isEdit=this.mode==='edit';
        const url=isEdit?'/masters/sub-kras/'+this.form._id:'/masters/sub-kras';
        const body={ kra_id:this.form.kra_id, name:this.form.name, weightage:this.form.weightage, unit:this.form.unit, measure_type:this.form.measure_type, logic_id:this.form.logic_id, review_period:this.form.review_period, description:this.form.description, is_active:this.form.is_active };
        try{
            const res=await fetch(url,{method:isEdit?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(body)});
            const data=await res.json();
            if(data.success){window.showToast(data.message,'success');this.showModal=false;setTimeout(()=>location.reload(),800);}
            else{window.showToast(data.message||'Error','error');}
        }catch(e){window.showToast('Network error','error');}
        finally{this.loading=false;}
    },
    async deleteItem(){
        this.loading=true;
        try{
            const res=await fetch('/masters/sub-kras/'+this.deleteId,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}});
            const data=await res.json();
            if(data.success){window.showToast(data.message,'success');this.showDelete=false;setTimeout(()=>location.reload(),800);}
            else{window.showToast(data.message||'Error','error');}
        }catch(e){window.showToast('Network error','error');}
        finally{this.loading=false;}
    }
}">
    <div class="flex items-center justify-between mb-5">
        <div><h2 class="text-base font-bold text-slate-800">Sub-KRA Master</h2><p class="text-xs text-slate-500 mt-0.5">Manage Sub Key Result Areas</p></div>
        <button @click="openCreate()" class="px-3 py-2 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Sub-KRA
        </button>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">KRA</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Weightage</th>
                    <th class="px-4 py-3 text-left">Unit</th>
                    <th class="px-4 py-3 text-left">Logic</th>
                    <th class="px-4 py-3 text-left">Review Period</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($subKras as $i => $sub)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-400">{{ $i+1 }}</td>
                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $sub->kra->name }}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $sub->name }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-medium bg-teal-50 text-teal-700">{{ $sub->weightage }}%</span></td>
                    <td class="px-4 py-3 text-slate-600">{{ $sub->unit }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ $sub->logic->name }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700">{{ $sub->review_period }}</span></td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $sub->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $sub->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button @click="openEdit({{ $sub->id }})" class="p-1 text-slate-400 hover:text-blue-600 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button @click="confirmDelete({{ $sub->id }})" class="p-1 text-slate-400 hover:text-red-600 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-10 text-center text-slate-400">No Sub-KRAs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div @click="showModal=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-screen overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800" x-text="mode==='create'?'Add Sub-KRA':'Edit Sub-KRA'"></h3>
                <button @click="showModal=false" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form @submit.prevent="submit()">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">KRA *</label>
                            <select x-model="form.kra_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="">Select KRA</option>
                                @foreach($kras as $kra)<option value="{{ $kra->id }}">{{ $kra->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Logic *</label>
                            <select x-model="form.logic_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="">Select Logic</option>
                                @foreach($logics as $logic)<option value="{{ $logic->id }}">{{ $logic->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Name *</label>
                        <input type="text" x-model="form.name" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Weightage (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" x-model="form.weightage" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Unit *</label>
                            <select x-model="form.unit" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="%">%</option><option value="Day">Day</option><option value="Count">Count</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Review Period *</label>
                            <select x-model="form.review_period" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="Monthly">Monthly</option><option value="Quarterly">Quarterly</option><option value="Annually">Annually</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Measure Type</label>
                        <input type="text" x-model="form.measure_type" placeholder="e.g. Percentage, Days" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                        <textarea x-model="form.description" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none resize-none"></textarea>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" x-model="form.is_active" class="w-4 h-4 text-teal-600 border-slate-300 rounded"><span class="text-sm text-slate-700">Active</span></label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showModal=false" class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" :disabled="loading" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="loading?'Saving...':'Save'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- Delete --}}
    <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div @click="showDelete=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center" @click.stop>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Delete Sub-KRA?</h3>
            <p class="text-sm text-slate-500 mb-6">This action cannot be undone.</p>
            <div class="flex gap-3 justify-center">
                <button @click="showDelete=false" class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                <button @click="deleteItem()" :disabled="loading" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection
