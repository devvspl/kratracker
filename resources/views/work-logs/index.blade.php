@extends('layouts.app')
@section('content')

    <style>
        .ts-control {
            border-radius: 0.5rem;
            border-color: #cbd5e1;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            min-height: 38px;
            box-shadow: none;
            outline: none;
            transition: all 0.2s;
        }

        .ts-control.focus {
            border-color: #14b8a6;
            box-shadow: 0 0 0 2px rgba(20, 184, 166, 0.2);
        }

        .ts-wrapper.single .ts-control:after {
            right: 1rem;
            border-color: #94a3b8 transparent transparent transparent;
            border-width: 5px 4px 0 4px;
        }

        .ts-dropdown {
            border-radius: 0.5rem;
            border-color: #cbd5e1;
            font-size: 0.875rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .ts-dropdown .option {
            padding: 0.5rem 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ts-control>.item {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: calc(100% - 20px);
            display: block;
        }
    </style>

    @php $defaultPriorityId = App\Models\Priority::where('name', 'Common')->value('id') ?? ''; @endphp

    <script>
    function workLogPage() {
        return {
            showModal: false, showDelete: false, showFeedback: false,
            modalMode: 'create', loading: false, deleteId: null,
            feedbackLogId: null, feedbacks: [],
            feedbackForm: { feedback_type: 'self', comment: '', rating: 5 },
            formData: {
                sub_kra_id: '', application_id: '', module_id: '', title: '', description: '',
                log_date: new Date().toISOString().split('T')[0],
                priority_id: '{{ $defaultPriorityId }}', status_id: '', achievement_value: 1,
                total_duration: 0, actual_duration: 0, test_status: '', testing_details: '',
                remark: '', notify_contact_ids: [], notify_user_ids: []
            },
            showCustomEmail: false,
            customEmail: { subject: '', body: '' },
            selectedId: null, activeTab: 'general', showFilters: false,
            filters: {
                date_from: '{{ request()->has("date_from") ? request("date_from") : date("Y-m-d") }}',
                date_to:   '{{ request()->has("date_to")   ? request("date_to")   : date("Y-m-d") }}',
                sub_kra_id:     '{{ request("sub_kra_id") }}',
                status_id:      '{{ request("status_id") }}',
                test_status:    '{{ request("test_status") }}',
                application_id: '{{ request("application_id") }}',
                module_id:      '{{ request("module_id") }}'
            },
            get activeFilterCount() {
                let c = 0;
                if (this.filters.sub_kra_id)     c++;
                if (this.filters.status_id)      c++;
                if (this.filters.test_status)    c++;
                if (this.filters.application_id) c++;
                if (this.filters.module_id)      c++;
                return c;
            },
            csrf() { return document.querySelector('meta[name=csrf-token]').content; },

            openCreate() {
                this.modalMode = 'create'; this.selectedId = null; this.activeTab = 'general';
                this.showCustomEmail = false;
                this.formData = { sub_kra_id:'', application_id:'', module_id:'', title:'', description:'',
                    log_date: new Date().toISOString().split('T')[0],
                    priority_id:'{{ $defaultPriorityId }}', status_id:'', achievement_value:1,
                    total_duration:0, actual_duration:0, test_status:'', testing_details:'',
                    remark:'', notify_contact_ids:[], notify_user_ids:[] };
                this.showModal = true;
            },

            async openEdit(id) {
                this.modalMode = 'edit'; this.selectedId = id; this.loading = true;
                this.activeTab = 'general'; this.showCustomEmail = false;
                try {
                    const res  = await fetch('/work-logs/' + id + '/show');
                    const data = await res.json();
                    if (data.success) {
                        const d = data.data;
                        this.formData = {
                            sub_kra_id: d.sub_kra_id, application_id: d.application_id||'',
                            module_id: d.module_id||'', title: d.title, description: d.description||'',
                            log_date: (d.log_date||'').substring(0,10),
                            priority_id: d.priority_id, status_id: d.status_id,
                            achievement_value: d.achievement_value||1,
                            total_duration: d.total_duration||0, actual_duration: d.actual_duration||0,
                            test_status: d.test_status||'', testing_details: d.testing_details||'',
                            remark: d.remark||'', notify_contact_ids:[], notify_user_ids:[]
                        };
                        this.showModal = true;
                    }
                } catch(e) { window.showToast('Error loading record','error'); }
                finally { this.loading = false; }
            },

            confirmDelete(id) { this.deleteId = id; this.showDelete = true; },

            async openFeedback(id) {
                this.feedbackLogId = id;
                this.feedbackForm = { feedback_type:'self', comment:'', rating:5 };
                try {
                    const res  = await fetch('/work-logs/' + id + '/show');
                    const data = await res.json();
                    if (data.success) this.feedbacks = data.data.feedbacks || [];
                } catch(e) {}
                this.showFeedback = true;
            },

            async submitForm() {
                if (!this.formData.title || !this.formData.sub_kra_id) {
                    this.activeTab = 'general';
                    return window.showToast('Please fill out Task Title and Sub-KRA','error');
                }
                if (!this.formData.log_date || !this.formData.status_id) {
                    this.activeTab = 'status';
                    return window.showToast('Please fill out Log Date and Status','error');
                }
                this.loading = true;
                const isEdit = this.modalMode === 'edit';
                const url = isEdit ? '/work-logs/' + this.selectedId + '/update' : '/work-logs/store';
                try {
                    const res  = await fetch(url, { method: isEdit ? 'PUT' : 'POST',
                        headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': this.csrf() },
                        body: JSON.stringify(this.formData) });
                    const data = await res.json();
                    if (res.status === 422) {
                        const errors = data.errors ? Object.values(data.errors).flat().join('\n') : data.message;
                        return window.showToast(errors || 'Validation error','error');
                    }
                    if (res.ok && data.success) { window.showToast(data.message,'success'); this.showModal=false; setTimeout(()=>location.reload(),800); }
                    else window.showToast(data.message||'Validation error','error');
                } catch(e) { window.showToast('Network error','error'); }
                finally { this.loading = false; }
            },

            async deleteLog() {
                this.loading = true;
                try {
                    const res  = await fetch('/work-logs/' + this.deleteId + '/delete', { method:'DELETE', headers:{'X-CSRF-TOKEN':this.csrf()} });
                    const data = await res.json();
                    if (data.success) { window.showToast(data.message,'success'); this.showDelete=false; setTimeout(()=>location.reload(),800); }
                    else window.showToast(data.message||'Error','error');
                } catch(e) { window.showToast('Network error','error'); }
                finally { this.loading = false; }
            },

            async submitFeedback() {
                this.loading = true;
                try {
                    const res  = await fetch('/work-logs/' + this.feedbackLogId + '/feedback', { method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf()},
                        body: JSON.stringify(this.feedbackForm) });
                    const data = await res.json();
                    if (data.success) { window.showToast(data.message,'success'); this.feedbacks.push(data.data); this.feedbackForm={feedback_type:'self',comment:'',rating:5}; }
                    else window.showToast(data.message||'Error','error');
                } catch(e) { window.showToast('Network error','error'); }
                finally { this.loading = false; }
            },

            openCustomEmail() {
                const title = this.formData.title || 'Task Update';
                const date  = this.formData.log_date || new Date().toISOString().substring(0,10);
                this.customEmail.subject = 'Task Update: "' + title + '" — ' + date;
                this.customEmail.body    = 'Hi,\n\nI wanted to share an update on the following task:\n\nTask: ' + title + '\nDate: ' + date + '\nStatus: (please check the system for current status)\n\nPlease review and let me know if you have any questions.\n\nBest regards';
                this.showCustomEmail = true;
            },

            async sendCustomEmail() {
                if (!this.customEmail.subject || !this.customEmail.body) return window.showToast('Subject and body are required','error');
                const total = (this.formData.notify_contact_ids||[]).length + (this.formData.notify_user_ids||[]).length;
                if (!total) return window.showToast('Select at least one recipient first','error');
                this.loading = true;
                try {
                    const res  = await fetch('/work-logs/send-custom-email', { method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf()},
                        body: JSON.stringify({ subject: this.customEmail.subject, body: this.customEmail.body,
                            contact_ids: this.formData.notify_contact_ids||[], user_ids: this.formData.notify_user_ids||[] }) });
                    const data = await res.json();
                    if (data.success) { window.showToast(data.message,'success'); this.showCustomEmail=false; }
                    else window.showToast(data.message||'Error','error');
                } catch(e) { window.showToast('Network error','error'); }
                finally { this.loading = false; }
            },

            applyFilters() {
                const params = new URLSearchParams();
                Object.keys(this.filters).forEach(k => { if (this.filters[k]) params.append(k, this.filters[k]); });
                window.location.href = '{{ route("work-logs.index") }}?' + params.toString();
            },

            async loadModules(appId) {
                const url = '/api/modules' + (appId ? '?application_id=' + appId : '');
                try {
                    const res  = await fetch(url);
                    const data = await res.json();
                    const ts   = window._moduleTomSelectInstance;
                    if (!ts) return;
                    ts.clearOptions();
                    ts.addOption({ value:'', text:'None' });
                    data.forEach(m => ts.addOption({ value: m.id, text: m.name }));
                    ts.refreshOptions(false);
                    this.formData.module_id = '';
                    ts.setValue('', true);
                } catch(e) {}
            },

            async loadFilterModules(appId) {
                const url = '/api/modules' + (appId ? '?application_id=' + appId : '');
                try {
                    const res  = await fetch(url);
                    const data = await res.json();
                    const ts   = window._filterModuleTomSelect;
                    if (!ts) return;
                    ts.clearOptions();
                    ts.addOption({ value:'', text:'All' });
                    data.forEach(m => ts.addOption({ value: m.id, text: m.name }));
                    ts.refreshOptions(false);
                    this.filters.module_id = '';
                    ts.setValue('', true);
                } catch(e) {}
            }
        };
    }
    </script>

    <div x-data="workLogPage()">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-800">Daily Work Logs</h2>
                <p class="text-xs text-slate-500 mt-0.5">Track your daily KRA activities</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Date From --}}
                <div class="flex items-center gap-1.5">
                    <label class="text-xs font-medium text-slate-500 whitespace-nowrap">From</label>
                    <input type="text" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" x-model="filters.date_from"
                        class="w-32 px-2.5 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer bg-white">
                </div>
                {{-- Date To --}}
                <div class="flex items-center gap-1.5">
                    <label class="text-xs font-medium text-slate-500 whitespace-nowrap">To</label>
                    <input type="text" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" x-model="filters.date_to"
                        class="w-32 px-2.5 py-2 text-xs border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer bg-white">
                </div>
                {{-- Apply date filter on Enter or blur is handled via applyFilters button --}}
                <button @click="applyFilters()"
                    class="px-3 py-2 bg-slate-700 text-white text-xs font-medium rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                    Go
                </button>
                <a href="{{ route('work-logs.index') }}"
                    class="px-3 py-2 bg-white border border-slate-300 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                    Clear
                </a>
                <div class="w-px h-6 bg-slate-200"></div>
                <button @click="showFilters = !showFilters"
                    class="px-3 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 flex items-center gap-1.5 transition-colors shadow-sm relative">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span x-text="showFilters ? 'Hide Filters' : 'More Filters'"></span>
                    <span x-show="activeFilterCount > 0" x-text="activeFilterCount"
                        class="ml-1 px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold"
                        style="display: none; font-size: 10px;"></span>
                </button>
                <button @click="openCreate()"
                    class="px-3 py-2 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 flex items-center gap-1.5 shadow-sm transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Work Log
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div x-show="showFilters" x-transition.opacity.duration.300ms
            class="bg-white rounded-lg border border-slate-200 p-4 mb-5 shadow-sm" style="display: none;">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sub-KRA</label>
                    <div x-id="['filter-sub-kra-select']">
                        <select :id="$id('filter-sub-kra-select')" x-model="filters.sub_kra_id" x-init="setTimeout(() => { let ts = new TomSelect($el, { create: false, placeholder: 'All' });
                            $watch('filters.sub_kra_id', val => ts.setValue(val, true));
                            ts.on('change', val => filters.sub_kra_id = val); }, 100)"
                            class="w-full" placeholder="All">
                            <option value="">All</option>
                            @foreach ($subKras as $subKra)
                                <option value="{{ $subKra->id }}">{{ $subKra->kra->name }} — {{ $subKra->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Application</label>
                    <div x-id="['filter-app-select']">
                        <select :id="$id('filter-app-select')" x-model="filters.application_id" x-init="setTimeout(() => {
                            let ts = new TomSelect($el, { create: false, placeholder: 'All' });
                            $watch('filters.application_id', val => { ts.setValue(val, true);
                                loadFilterModules(val); });
                            ts.on('change', val => { filters.application_id = val;
                                loadFilterModules(val); });
                        }, 100)"
                            class="w-full">
                            <option value="">All</option>
                            @foreach ($applications as $app)
                                <option value="{{ $app->id }}">{{ $app->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Module</label>
                    <div x-id="['filter-module-select']">
                        <select :id="$id('filter-module-select')" x-model="filters.module_id" x-init="setTimeout(() => {
                            window._filterModuleTomSelect = new TomSelect($el, { create: false, placeholder: 'All' });
                            $watch('filters.module_id', val => window._filterModuleTomSelect.setValue(val, true));
                            window._filterModuleTomSelect.on('change', val => filters.module_id = val);
                        }, 120)"
                            class="w-full">
                            <option value="">All</option>
                            @foreach ($modules as $mod)
                                <option value="{{ $mod->id }}">{{ $mod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <div x-id="['filter-status-select']">
                        <select :id="$id('filter-status-select')" x-model="filters.status_id" x-init="setTimeout(() => { let ts = new TomSelect($el, { create: false, placeholder: 'All' });
                            $watch('filters.status_id', val => ts.setValue(val, true));
                            ts.on('change', val => filters.status_id = val); }, 100)"
                            class="w-full" placeholder="All">
                            <option value="">All</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Test Status</label>
                    <div x-id="['filter-test-status-select']">
                        <select :id="$id('filter-test-status-select')" x-model="filters.test_status" x-init="setTimeout(() => { let ts = new TomSelect($el, { create: false, placeholder: 'All' });
                            $watch('filters.test_status', val => ts.setValue(val, true));
                            ts.on('change', val => filters.test_status = val); }, 100)"
                            class="w-full" placeholder="All">
                            <option value="">All</option>
                            <option value="Pending">Pending</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                            <option value="Skipped">Skipped</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 items-end">
                    <button @click="applyFilters()"
                        class="flex-1 px-3 py-2 text-sm bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition-colors">Apply</button>
                    <a href="{{ route('work-logs.index') }}"
                        class="flex-1 text-center px-3 py-2 text-sm bg-slate-100 text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-200 transition-colors">Clear</a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Date</th>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">KRA / Sub-KRA</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Total Dur.</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Actual Dur.</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Test Status</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->log_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $log->title }}</p>
                                @if ($log->remark)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($log->remark, 40) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-slate-700">{{ $log->subKra->kra->name }}</p>
                                <p class="text-xs text-slate-400">{{ $log->subKra->name }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $log->total_duration ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $log->actual_duration ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($log->test_status)
                                    <span
                                        class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">{{ $log->test_status }}</span>
                                @else<span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-0.5 text-xs rounded-full font-medium bg-{{ $log->status->color_class }}-100 text-{{ $log->status->color_class }}-700">{{ $log->status->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <button @click="openEdit({{ $log->id }})" title="Edit"
                                        class="p-1 text-slate-400 hover:text-blue-600 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button @click="openFeedback({{ $log->id }})" title="Feedback"
                                        class="p-1 text-slate-400 hover:text-yellow-600 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                    </button>
                                    <button @click="confirmDelete({{ $log->id }})" title="Delete"
                                        class="p-1 text-slate-400 hover:text-red-600 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-slate-400">No work logs found. Click
                                "Add Work Log" to create your first entry.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $workLogs->links() }}</div>

        {{-- Delete Modal --}}
        <div x-show="showDelete" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                    @click="showDelete = false"></div>
                <div
                    class="relative bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center transform transition-all">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Work Log?</h3>
                    <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete this work log? This action
                        cannot be undone.</p>
                    <div class="flex gap-3">
                        <button @click="showDelete = false"
                            class="flex-1 px-4 py-2 bg-white border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                        <button @click="deleteLog()"
                            class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <span x-show="!loading">Delete</span>
                            <span x-show="loading">Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Create/Edit Modal --}}
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" @click="showModal = false">
                </div>
                <div
                    class="relative inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle">

                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800"
                            x-text="modalMode === 'create' ? 'Add New Work Log' : 'Edit Work Log'"></h3>
                        <button type="button" @click="showModal = false"
                            class="text-slate-400 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Tabs Navigation --}}
                    <div class="px-6 pt-3 flex gap-6 border-b border-slate-200 bg-slate-50/50">
                        <button type="button" @click="activeTab = 'general'"
                            :class="activeTab === 'general' ? 'border-teal-500 text-teal-700' :
                                'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="pb-2 text-sm font-medium border-b-2 transition-colors">1. General Info</button>
                        <button type="button" @click="activeTab = 'status'"
                            :class="activeTab === 'status' ? 'border-teal-500 text-teal-700' :
                                'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="pb-2 text-sm font-medium border-b-2 transition-colors">2. Status, Priority &
                            Testing</button>
                        <button type="button" @click="activeTab = 'metrics'"
                            :class="activeTab === 'metrics' ? 'border-teal-500 text-teal-700' :
                                'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="pb-2 text-sm font-medium border-b-2 transition-colors">3. Metrics & Duration</button>
                    </div>

                    {{-- ✅ Form wraps BOTH the scrollable content AND the footer --}}
                    <form @submit.prevent="submitForm" novalidate>

                        <div class="p-6 min-h-[280px] overflow-y-auto">

                            {{-- Tab 1: General Info --}}
                            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Task Title <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" x-model="formData.title"
                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all placeholder:text-slate-400"
                                            placeholder="e.g. Developed new reporting module">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                                        <textarea x-model="formData.description" rows="1"
                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all placeholder:text-slate-400"
                                            placeholder="Optional details..."></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Sub-KRA <span
                                                class="text-red-500">*</span></label>
                                        <div x-id="['sub-kra-select']">
                                            <select :id="$id('sub-kra-select')" x-model="formData.sub_kra_id"
                                                x-init="setTimeout(() => { let ts = new TomSelect($el, { create: false, placeholder: 'Select Sub-KRA' });
                                                    $watch('formData.sub_kra_id', val => ts.setValue(val, true));
                                                    ts.on('change', val => formData.sub_kra_id = val); }, 100)" class="w-full" placeholder="Select Sub-KRA">
                                                <option value="">Select Sub-KRA</option>
                                                @foreach ($subKras as $subKra)
                                                    <option value="{{ $subKra->id }}">{{ $subKra->kra->name }} —
                                                        {{ $subKra->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Application</label>
                                        <div x-id="['app-select']">
                                            <select :id="$id('app-select')" x-model="formData.application_id"
                                                x-init="setTimeout(() => {
                                                    let ts = new TomSelect($el, { create: false, placeholder: 'None' });
                                                    $watch('formData.application_id', val => { ts.setValue(val, true);
                                                        loadModules(val); });
                                                    ts.on('change', val => { formData.application_id = val;
                                                        loadModules(val); });
                                                }, 100)" class="w-full" placeholder="None">
                                                <option value="">None</option>
                                                @foreach ($applications as $app)
                                                    <option value="{{ $app->id }}">{{ $app->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Module</label>
                                        <div x-id="['module-select']">
                                            <select :id="$id('module-select')" x-model="formData.module_id"
                                                x-init="setTimeout(() => {
                                                    const self = $data;
                                                    window._moduleTomSelectInstance = new TomSelect($el, {
                                                        create: async function(input, callback) {
                                                            try {
                                                                const res = await fetch('/api/modules', {
                                                                    method: 'POST',
                                                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                                                    body: JSON.stringify({ name: input, application_id: self.formData.application_id || null })
                                                                });
                                                                const data = await res.json();
                                                                if (data.success) { callback({ value: String(data.data.id), text: data.data.name }); } else { callback(); }
                                                            } catch (e) { callback(); }
                                                        },
                                                        placeholder: 'Select or type module...'
                                                    });
                                                    $watch('formData.module_id', val => window._moduleTomSelectInstance.setValue(val, true));
                                                    window._moduleTomSelectInstance.on('change', val => formData.module_id = val);
                                                }, 150)" class="w-full"
                                                placeholder="Select or type module...">
                                                <option value="">None</option>
                                                @foreach ($modules as $mod)
                                                    <option value="{{ $mod->id }}">{{ $mod->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Status, Priority & Testing --}}
                            <div x-show="activeTab === 'status'" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Log Date <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" x-model="formData.log_date"
                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all cursor-pointer bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Status <span
                                                class="text-red-500">*</span></label>
                                        <div x-id="['status-select']">
                                            <select :id="$id('status-select')" x-model="formData.status_id"
                                                x-init="setTimeout(() => { let ts = new TomSelect($el, { create: false, placeholder: 'Select Status' });
                                                    $watch('formData.status_id', val => ts.setValue(val, true));
                                                    ts.on('change', val => formData.status_id = val); }, 100)" class="w-full" placeholder="Select Status">
                                                <option value="">Select Status</option>
                                                @foreach ($statuses as $status)
                                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Priority</label>
                                        <div x-id="['priority-select']">
                                            <select :id="$id('priority-select')" x-model="formData.priority_id"
                                                x-init="setTimeout(() => { let ts = new TomSelect($el, { create: false, placeholder: 'Select Priority' });
                                                    $watch('formData.priority_id', val => ts.setValue(val, true));
                                                    ts.on('change', val => formData.priority_id = val); }, 100)" class="w-full" placeholder="Select Priority">
                                                <option value="">Select Priority</option>
                                                @foreach ($priorities as $priority)
                                                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Test Status</label>
                                        <div x-id="['test-status-select']">
                                            <select :id="$id('test-status-select')" x-model="formData.test_status"
                                                x-init="setTimeout(() => { let ts = new TomSelect($el, { create: false, placeholder: 'N/A' });
                                                    $watch('formData.test_status', val => ts.setValue(val, true));
                                                    ts.on('change', val => formData.test_status = val); }, 100)" class="w-full" placeholder="N/A">
                                                <option value="">N/A</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Passed">Passed</option>
                                                <option value="Failed">Failed</option>
                                                <option value="Skipped">Skipped</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Testing
                                            Details</label>
                                        <textarea x-model="formData.testing_details" rows="2"
                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all placeholder:text-slate-400"
                                            placeholder="Details regarding testing (logs, issues, outcomes...)"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Remark</label>
                                        <input type="text" x-model="formData.remark"
                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all placeholder:text-slate-400"
                                            placeholder="Special note or remark">
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 3: Metrics & Duration --}}
                            <div x-show="activeTab === 'metrics'" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Total Duration
                                            (Hours)</label>
                                        <input type="number" step="0.01" x-model.number="formData.total_duration"
                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Actual Duration
                                            (Hours)</label>
                                        <input type="number" step="0.01" x-model.number="formData.actual_duration"
                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Difference
                                            (Calculated)</label>
                                        <div
                                            class="px-3 py-2 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg h-[38px] flex items-center">
                                            <span
                                                x-text="(Number(formData.total_duration || 0) - Number(formData.actual_duration || 0)).toFixed(2) + ' Hrs'"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notify Recipients --}}
                                <div class="mt-4 pt-3 border-t border-slate-100" x-data="{ search: '' }">
                                    <p class="text-xs font-semibold text-slate-600 mb-2">
                                        Notify via Email
                                        <span class="text-slate-400 font-normal ml-1">(optional)</span>
                                    </p>
                                    <input type="text" x-model="search" placeholder="Search contacts or users..."
                                        class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-teal-500 mb-2 bg-white">

                                    @php
                                        $allRecipients = collect();
                                        foreach($contacts as $c) $allRecipients->push(['id' => 'c_'.$c->id, 'real_id' => $c->id, 'type' => 'contact', 'name' => $c->name, 'email' => $c->email]);
                                        foreach($notifyUsers as $u) $allRecipients->push(['id' => 'u_'.$u->id, 'real_id' => $u->id, 'type' => 'user', 'name' => $u->name, 'email' => $u->email]);
                                    @endphp

                                    <div class="max-h-32 overflow-y-auto space-y-1 pr-1">
                                        @foreach($allRecipients as $r)
                                        <label x-show="search === '' || '{{ strtolower($r['name']) }} {{ strtolower($r['email']) }}'.includes(search.toLowerCase())"
                                            class="flex items-center gap-2 cursor-pointer px-2.5 py-1.5 rounded-lg border border-slate-100 hover:bg-teal-50 hover:border-teal-200 transition-colors text-xs">
                                            <input type="checkbox" value="{{ $r['real_id'] }}"
                                                @change="
                                                    const id = {{ $r['real_id'] }};
                                                    const type = '{{ $r['type'] }}';
                                                    const key = type === 'contact' ? 'notify_contact_ids' : 'notify_user_ids';
                                                    if (!formData[key]) formData[key] = [];
                                                    if ($event.target.checked) { formData[key].push(id); }
                                                    else { formData[key] = formData[key].filter(x => x !== id); }
                                                "
                                                :checked="(formData.{{ $r['type'] === 'contact' ? 'notify_contact_ids' : 'notify_user_ids' }} || []).includes({{ $r['real_id'] }})"
                                                class="w-3.5 h-3.5 text-teal-600 rounded shrink-0">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $r['type'] === 'user' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $r['type'] === 'user' ? 'User' : 'Contact' }}
                                            </span>
                                            <span class="font-medium text-slate-700 truncate">{{ $r['name'] }}</span>
                                            <span class="text-slate-400 truncate">&lt;{{ $r['email'] }}&gt;</span>
                                        </label>
                                        @endforeach
                                    </div>

                                    {{-- Custom Email Composer --}}
                                    <div class="mt-3 pt-3 border-t border-slate-100">
                                        <button type="button" @click="openCustomEmail()"
                                            class="flex items-center gap-1.5 text-xs font-medium text-teal-600 hover:text-teal-700 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            Compose Custom Email to Selected Recipients
                                        </button>

                                        <div x-show="showCustomEmail" x-transition class="mt-3 space-y-2" style="display:none;">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Subject</label>
                                                <input type="text" x-model="customEmail.subject"
                                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none"
                                                    placeholder="Email subject...">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                                    Message Body
                                                    <span class="text-slate-400 font-normal ml-1">— edit the auto-suggested content below</span>
                                                </label>
                                                <textarea x-model="customEmail.body" rows="5"
                                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none resize-y font-mono"
                                                    placeholder="Write your message..."></textarea>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="sendCustomEmail()" :disabled="loading"
                                                    class="px-4 py-1.5 text-xs font-semibold bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-1.5 transition-colors">
                                                    <svg x-show="loading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                                    <svg x-show="!loading" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                    <span x-text="loading ? 'Sending...' : 'Send Email'"></span>
                                                </button>
                                                <button type="button" @click="showCustomEmail = false"
                                                    class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700 transition-colors">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 flex justify-end gap-3 border-t border-slate-100 bg-slate-50/50">
                            <button type="button" x-show="activeTab !== 'general'"
                                @click="activeTab = activeTab === 'metrics' ? 'status' : 'general'"
                                class="px-5 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors mr-auto">Back</button>

                            <button type="button" @click="showModal = false"
                                class="px-5 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>

                            <button type="button" x-show="activeTab !== 'metrics'"
                                @click="activeTab = activeTab === 'general' ? 'status' : 'metrics'"
                                class="px-5 py-2 text-sm font-medium text-white bg-slate-800 rounded-lg hover:bg-slate-900 transition-colors">Next
                                Step</button>

                            <button type="submit" x-show="activeTab === 'metrics'"
                                class="px-5 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors flex items-center gap-2"
                                :disabled="loading">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none"
                                    viewBox="0 0 24 24" style="display: none;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="loading ? 'Saving...' : 'Save Work Log'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- Feedback Modal --}}
        <div x-show="showFeedback" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm"
                    @click="showFeedback = false"></div>
                <div
                    class="relative inline-block w-full max-w-2xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle p-6">

                    <div class="flex justify-between items-center mb-5 border-b pb-3">
                        <h3 class="text-lg font-bold text-slate-800">Task Feedback & Reviews</h3>
                        <button @click="showFeedback = false" class="text-slate-400 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6 max-h-64 overflow-y-auto bg-slate-50 p-4 rounded-lg">
                        <template x-if="feedbacks.length === 0">
                            <p class="text-sm text-slate-400 text-center py-4">No feedback provided yet.</p>
                        </template>
                        <template x-for="fb in feedbacks" :key="fb.id">
                            <div class="mb-3 p-3 bg-white border border-slate-200 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold font-medium"
                                        :class="fb.feedback_type == 'self' ? 'text-blue-600' : 'text-purple-600'"
                                        x-text="fb.feedback_type == 'self' ? 'Self Review' : 'Manager Review'"></span>
                                    <span class="text-xs text-slate-400"
                                        x-text="new Date(fb.created_at).toLocaleDateString()"></span>
                                </div>
                                <p class="text-sm text-slate-700" x-text="fb.comment"></p>
                                <div class="mt-2 text-xs flex items-center gap-1 font-medium text-amber-500">
                                    Rating: <span x-text="fb.rating"></span> / 5
                                </div>
                            </div>
                        </template>
                    </div>

                    <form @submit.prevent="submitFeedback" class="border-t border-slate-100 pt-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Add New Feedback</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Feedback Type</label>
                                <select x-model="feedbackForm.feedback_type" required
                                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                                    <option value="self">Self Review</option>
                                    <option value="manager">Manager Review</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Rating (1-5)</label>
                                <input type="number" min="1" max="5" x-model="feedbackForm.rating"
                                    required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Comment</label>
                                <textarea x-model="feedbackForm.comment" required rows="2"
                                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400"
                                    placeholder="Your thoughts on this task..."></textarea>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit"
                                class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center gap-2"
                                :disabled="loading">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none"
                                    viewBox="0 0 24 24" style="display: none;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-show="!loading">Post Feedback</span>
                                <span x-show="loading">Posting...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
