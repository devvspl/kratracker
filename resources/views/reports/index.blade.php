@extends('layouts.app')
@section('content')
    @php
        $configsJson = json_encode(
            $configs
                ->map(
                    fn($c) => [
                        'id' => $c->id,
                        'recipient_user_id' => $c->recipient_user_id,
                        'recipient_name' => optional($c->recipient)->name,
                        'employee_user_id' => $c->employee_user_id,
                        'employee_name' => optional($c->employee)->name ?? 'All Employees',
                        'report_type' => $c->report_type,
                        'is_active' => $c->is_active,
                        'last_sent_at' => $c->last_sent_at?->diffForHumans(),
                    ],
                )
                ->values()
                ->toArray(),
        );

        $contactsJson = json_encode(
            $contacts
                ->map(
                    fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'email' => $c->email,
                        'role' => $c->role ?? '',
                        'notes' => $c->notes ?? '',
                        'notify_on_complete' => $c->notify_on_complete,
                        'notify_on_status_change' => $c->notify_on_status_change,
                        'notify_on_daily_report' => $c->notify_on_daily_report,
                        'notify_on_weekly_report' => $c->notify_on_weekly_report,
                        'notify_on_monthly_report' => $c->notify_on_monthly_report,
                        'is_active' => $c->is_active,
                    ],
                )
                ->values()
                ->toArray(),
        );
    @endphp

    <script>
        function reportPage() {
            return {
                activeTab: '{{ request("tab", "reports") }}',
                configs: {!! $configsJson !!},
                contacts: {!! $contactsJson !!},
                showModal: false,
                showSend: false,
                showDelete: false,
                showContactModal: false,
                showContactDelete: false,
                showCustomMail: false,
                showContactReport: false,
                mode: 'create',
                loading: false,
                deleteId: null,
                contactDeleteId: null,
                form: {
                    recipient_user_id: '',
                    employee_user_id: '',
                    report_type: 'daily',
                    is_active: true
                },
                sendForm: {
                    recipient_user_id: '',
                    employee_user_id: '',
                    report_type: 'daily',
                    date_from: '{{ now()->startOfMonth()->toDateString() }}',
                    date_to: '{{ now()->toDateString() }}'
                },
                contactForm: {
                    name: '',
                    email: '',
                    role: '',
                    notes: '',
                    notify_on_complete: true,
                    notify_on_status_change: false,
                    notify_on_daily_report: false,
                    notify_on_weekly_report: false,
                    notify_on_monthly_report: false,
                    is_active: true
                },
                customMailForm: {
                    contact_id: '',
                    subject: '',
                    body: ''
                },
                contactReportForm: {
                    contact_id: '',
                    employee_id: '',
                    report_type: 'daily',
                    date_from: '{{ now()->startOfMonth()->toDateString() }}',
                    date_to: '{{ now()->toDateString() }}'
                },

                csrf() {
                    return document.querySelector('meta[name=csrf-token]').content;
                },

                openCreate() {
                    this.mode = 'create';
                    this.form = {
                        recipient_user_id: '',
                        employee_user_id: '',
                        report_type: 'daily',
                        is_active: true
                    };
                    this.showModal = true;
                },
                openEdit(id) {
                    this.mode = 'edit';
                    const c = this.configs.find(x => x.id === id);
                    if (!c) return;
                    this.form = {
                        recipient_user_id: c.recipient_user_id,
                        employee_user_id: c.employee_user_id || '',
                        report_type: c.report_type,
                        is_active: c.is_active,
                        _id: id
                    };
                    this.showModal = true;
                },
                confirmDelete(id) {
                    this.deleteId = id;
                    this.showDelete = true;
                },

                async submit() {
                    this.loading = true;
                    const isEdit = this.mode === 'edit';
                    const url = isEdit ? '/reports/' + this.form._id : '/reports';
                    const body = {
                        recipient_user_id: this.form.recipient_user_id,
                        employee_user_id: this.form.employee_user_id || null,
                        report_type: this.form.report_type,
                        is_active: this.form.is_active
                    };
                    try {
                        const res = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        if (res.status === 422) return window.showToast(data.message || 'Validation error', 'error');
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            this.showModal = false;
                            setTimeout(() => location.href = '/reports?tab=' + this.activeTab, 800);
                        } else window.showToast(data.message || 'Error', 'error');
                    } catch (e) {
                        window.showToast('Network error', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteItem() {
                    this.loading = true;
                    try {
                        const res = await fetch('/reports/' + this.deleteId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf()
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            this.showDelete = false;
                            setTimeout(() => location.href = '/reports?tab=' + this.activeTab, 800);
                        } else window.showToast(data.message || 'Error', 'error');
                    } catch (e) {
                        window.showToast('Network error', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async sendNow() {
                    this.loading = true;
                    const body = {
                        recipient_user_id: this.sendForm.recipient_user_id,
                        employee_user_id: this.sendForm.employee_user_id || null,
                        report_type: this.sendForm.report_type,
                        date_from: this.sendForm.date_from,
                        date_to: this.sendForm.date_to
                    };
                    try {
                        const res = await fetch('/reports/send-now', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            this.showSend = false;
                        } else window.showToast(data.message || 'Error', 'error');
                    } catch (e) {
                        window.showToast('Network error', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                openContactCreate() {
                    this.contactForm = {
                        name: '',
                        email: '',
                        role: '',
                        notes: '',
                        notify_on_complete: true,
                        notify_on_status_change: false,
                        notify_on_daily_report: false,
                        notify_on_weekly_report: false,
                        notify_on_monthly_report: false,
                        is_active: true
                    };
                    this.mode = 'create';
                    this.showContactModal = true;
                },
                openContactEdit(id) {
                    const c = this.contacts.find(x => x.id === id);
                    if (!c) return;
                    this.contactForm = {
                        ...c,
                        _id: id
                    };
                    this.mode = 'edit';
                    this.showContactModal = true;
                },

                async submitContact() {
                    this.loading = true;
                    const isEdit = this.mode === 'edit';
                    const url = isEdit ? '/contacts/' + this.contactForm._id : '/contacts';
                    const {
                        _id,
                        ...body
                    } = this.contactForm;
                    try {
                        const res = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        if (res.status === 422) return window.showToast(Object.values(data.errors || {}).flat().join(
                            ' ') || data.message, 'error');
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            this.showContactModal = false;
                            setTimeout(() => location.href = '/reports?tab=' + this.activeTab, 800);
                        } else window.showToast(data.message || 'Error', 'error');
                    } catch (e) {
                        window.showToast('Network error', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteContact() {
                    this.loading = true;
                    try {
                        const res = await fetch('/contacts/' + this.contactDeleteId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf()
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            this.showContactDelete = false;
                            setTimeout(() => location.href = '/reports?tab=' + this.activeTab, 800);
                        } else window.showToast(data.message || 'Error', 'error');
                    } catch (e) {
                        window.showToast('Network error', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async sendCustomMail() {
                    const subject = this.customMailForm.subject;
                    if (!subject) return window.showToast('Subject is required', 'error');

                    // Get TinyMCE content
                    const body = tinymce.get('custom-email-body') ? tinymce.get('custom-email-body').getContent() : '';
                    if (!body || body === '<p></p>' || body.trim() === '') return window.showToast(
                        'Message body is required', 'error');

                    // Get TomSelect values
                    const toTs = document.querySelector('#email-to-select')?._tomSelect;
                    const ccTs = document.querySelector('#email-cc-select')?._tomSelect;
                    const bccTs = document.querySelector('#email-bcc-select')?._tomSelect;
                    const toList = toTs ? Object.keys(toTs.items).map(k => toTs.options[k]?.value || k) : [];
                    const ccList = ccTs ? Object.keys(ccTs.items).map(k => ccTs.options[k]?.value || k) : [];
                    const bccList = bccTs ? Object.keys(bccTs.items).map(k => bccTs.options[k]?.value || k) : [];

                    if (!toList.length) return window.showToast('Add at least one To recipient', 'error');

                    this.loading = true;
                    try {
                        const res = await fetch('/contacts/send-custom', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify({
                                to: toList,
                                cc: ccList,
                                bcc: bccList,
                                subject,
                                body
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            this.showCustomMail = false;
                        } else window.showToast(data.message || 'Error', 'error');
                    } catch (e) {
                        window.showToast('Network error', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async sendContactReport() {
                    this.loading = true;
                    try {
                        const res = await fetch('/contacts/send-report', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify(this.contactReportForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            this.showContactReport = false;
                        } else window.showToast(data.message || 'Error', 'error');
                    } catch (e) {
                        window.showToast('Network error', 'error');
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>

    <div x-data="reportPage()">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-800">Reports & Contacts</h2>
                <p class="text-xs text-slate-500 mt-0.5">Automated reports, external contacts & status notifications</p>
            </div>
            <div class="flex items-center gap-2">
                <div x-show="activeTab === 'reports'" class="flex items-center gap-2">
                    <button @click="showSend = true"
                        class="px-3 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 flex items-center gap-1.5 shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Send Now
                    </button>
                    <button @click="openCreate()"
                        class="px-3 py-2 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 flex items-center gap-1.5 shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Config
                    </button>
                </div>
                <div x-show="activeTab === 'contacts'" class="flex items-center gap-2" style="display:none;">
                    <button @click="showContactReport = true"
                        class="px-3 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 flex items-center gap-1.5 shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Send Report
                    </button>
                    <button @click="showCustomMail = true"
                        class="px-3 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 flex items-center gap-1.5 shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Custom Email
                    </button>
                    <button @click="openContactCreate()"
                        class="px-3 py-2 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 flex items-center gap-1.5 shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Contact
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 mb-5 bg-white border border-slate-200 rounded-lg p-1 w-fit shadow-sm">
            <button @click="activeTab='reports'; history.replaceState(null,'','/reports?tab=reports')"
                :class="activeTab === 'reports' ? 'bg-teal-600 text-white shadow-sm' :
                    'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="px-4 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Report Configs
                <span class="px-1.5 py-0.5 rounded text-[10px]"
                    :class="activeTab === 'reports' ? 'bg-white/20' : 'bg-slate-100'">{{ $configs->count() }}</span>
            </button>
            <button @click="activeTab='contacts'; history.replaceState(null,'','/reports?tab=contacts')"
                :class="activeTab === 'contacts' ? 'bg-teal-600 text-white shadow-sm' :
                    'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="px-4 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Email Contacts
                <span class="px-1.5 py-0.5 rounded text-[10px]"
                    :class="activeTab === 'contacts' ? 'bg-white/20' : 'bg-slate-100'">{{ $contacts->count() }}</span>
            </button>
        </div>

        {{-- ═══ TAB: REPORT CONFIGS ═══ --}}
        <div x-show="activeTab==='reports'" x-transition.opacity.duration.150ms>
            <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 mb-5 flex flex-wrap gap-4 text-xs text-teal-700">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><strong>Daily</strong> — Weekdays at 08:00</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span><strong>Weekly</strong> — Every Monday at 08:30</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span><strong>Monthly</strong> — 1st of month at 09:00</span>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Recipient</th>
                            <th class="px-4 py-3 text-left">Employee</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Last Sent</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($configs as $i => $config)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800 text-xs">{{ optional($config->recipient)->name }}
                                    </p>
                                    <p class="text-xs text-slate-400">{{ optional($config->recipient)->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $config->employee_user_id ? optional($config->employee)->name : 'All Employees' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full font-semibold
                            {{ $config->report_type === 'daily' ? 'bg-blue-100 text-blue-700' : ($config->report_type === 'weekly' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700') }}">
                                        {{ ucfirst($config->report_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full font-medium {{ $config->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $config->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ $config->last_sent_at ? $config->last_sent_at->diffForHumans() : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <button @click="openEdit({{ $config->id }})"
                                            class="p-1 text-slate-400 hover:text-blue-600 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete({{ $config->id }})"
                                            class="p-1 text-slate-400 hover:text-red-600 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400 text-xs">No report configs
                                    yet. Click "Add Config" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- end reports tab --}}

        {{-- ═══ TAB: EMAIL CONTACTS ═══ --}}
        <div x-show="activeTab==='contacts'" x-transition.opacity.duration.150ms style="display:none;">

            <p class="text-xs text-slate-500 mb-4">External recipients for task notifications and reports. Emails are sent
                automatically based on their subscriptions.</p>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Name / Email</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Notes</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contacts as $contact)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800 text-xs">{{ $contact->name }}</p>
                                    <p class="text-slate-400 text-xs">{{ $contact->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $contact->role ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500 max-w-xs truncate">
                                    {{ $contact->notes ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium {{ $contact->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $contact->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <button @click="openContactEdit({{ $contact->id }})"
                                            class="p-1 text-slate-400 hover:text-blue-600 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="contactDeleteId={{ $contact->id }}; showContactDelete=true"
                                            class="p-1 text-slate-400 hover:text-red-600 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-400">No contacts yet. Click
                                    "Add Contact" to add external recipients.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- end contacts tab --}}

        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div @click="showModal=false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-bold text-slate-800"
                        x-text="mode === 'create' ? 'Add Report Config' : 'Edit Report Config'"></h3>
                    <button @click="showModal=false" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg></button>
                </div>
                <form @submit.prevent="submit()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Send Report To (Recipient) <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.recipient_user_id" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                            <option value="">Select recipient...</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}
                                    ({{ $u->roles->first()?->name ?? 'User' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Report About (Employee)</label>
                        <select x-model="form.employee_user_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                            <option value="">All Employees</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Leave blank to receive reports for all employees.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Report Type <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.report_type" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                            <option value="daily">Daily (Weekdays at 08:00)</option>
                            <option value="weekly">Weekly (Monday at 08:30)</option>
                            <option value="monthly">Monthly (1st at 09:00)</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active"
                            class="w-4 h-4 text-teal-600 border-slate-300 rounded">
                        <span class="text-sm text-slate-700">Active</span>
                    </label>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal=false"
                            class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                        <button type="submit" :disabled="loading"
                            class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            <span x-text="loading ? 'Saving...' : 'Save'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Send Now Modal --}}
        <div x-show="showSend" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div @click="showSend=false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop
                x-init="$watch('showSend', val => { if(val) setTimeout(() => {
                    const initTs = (id, model) => {
                        const el = document.getElementById(id);
                        if(!el || el._tomSelect) return;
                        const ts = new TomSelect(el, { create: false });
                        ts.on('change', v => { this[model.split('.')[0]][model.split('.')[1]] = v; });
                    };
                    initTs('send-to-select', 'sendForm.recipient_user_id');
                    initTs('send-emp-select', 'sendForm.employee_user_id');
                    initTs('send-type-select', 'sendForm.report_type');
                }, 100); })">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-bold text-slate-800">Send Report Now</h3>
                    <button @click="showSend=false" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg></button>
                </div>
                <form id="send-now-modal" @submit.prevent="sendNow()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Send To <span class="text-red-500">*</span></label>
                        <select id="send-to-select" x-model="sendForm.recipient_user_id" required
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}
                                    ({{ $u->roles->first()?->name ?? 'User' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Employee</label>
                        <select id="send-emp-select" x-model="sendForm.employee_user_id"
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Report Type <span
                                class="text-red-500">*</span></label>
                        <select id="send-type-select" x-model="sendForm.report_type" required
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date From</label>
                            <input type="date" x-model="sendForm.date_from"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                            <input type="date" x-model="sendForm.date_to"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showSend=false"
                            class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                        <button type="submit" :disabled="loading"
                            class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            <span x-text="loading ? 'Sending...' : 'Send Now'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Modal --}}
        <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div @click="showDelete=false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center" @click.stop>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg
                        class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg></div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Delete Config?</h3>
                <p class="text-sm text-slate-500 mb-6">This report will no longer be sent automatically.</p>
                <div class="flex gap-3 justify-center">
                    <button @click="showDelete=false"
                        class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button @click="deleteItem()" :disabled="loading"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">Delete</button>
                </div>
            </div>
        </div>

        {{-- Contact Add/Edit Modal --}}
        <div x-show="showContactModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div @click="showContactModal=false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto"
                @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-bold text-slate-800"
                        x-text="mode==='create' ? 'Add Contact' : 'Edit Contact'"></h3>
                    <button @click="showContactModal=false" class="text-slate-400 hover:text-slate-600"><svg
                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg></button>
                </div>
                <form @submit.prevent="submitContact()" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" x-model="contactForm.name" required
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" x-model="contactForm.email" required
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Role / Title</label>
                            <input type="text" x-model="contactForm.role"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Notes</label>
                            <input type="text" x-model="contactForm.notes"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="contactForm.is_active" class="w-4 h-4 text-teal-600 rounded">
                            <span class="text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showContactModal=false"
                            class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                        <button type="submit" :disabled="loading"
                            class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            <span x-text="loading ? 'Saving...' : 'Save'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Custom Email Modal --}}
        <style>
            #email-to-select ~ .ts-wrapper .ts-control,
            #email-cc-select ~ .ts-wrapper .ts-control,
            #email-bcc-select ~ .ts-wrapper .ts-control,
            .email-ts-field .ts-control {
                border: 1px solid #cbd5e1 !important;
                border-radius: 0.5rem !important;
                padding: 0.375rem 0.75rem !important;
                min-height: 38px !important;
                box-shadow: none !important;
                font-size: 0.875rem !important;
            }
            .email-ts-field .ts-control.focus,
            .email-ts-field .ts-wrapper.focus .ts-control {
                border-color: #14b8a6 !important;
                box-shadow: 0 0 0 2px rgba(20,184,166,0.2) !important;
            }
            .email-ts-field .ts-dropdown {
                border-radius: 0.5rem !important;
                border-color: #cbd5e1 !important;
                font-size: 0.875rem !important;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
            }
            .email-ts-field .ts-dropdown .option { padding: 0.5rem 0.75rem; }
            .email-ts-field .ts-control .item {
                background: #f0fdfa;
                color: #0f766e;
                border: 1px solid #99f6e4;
                border-radius: 0.375rem;
                padding: 1px 6px;
                font-size: 0.75rem;
                font-weight: 500;
            }
            .email-ts-field .ts-control .item .remove {
                color: #0f766e;
                border-left: 1px solid #99f6e4;
                margin-left: 4px;
                padding-left: 4px;                                                                                                                                                                  
            }
        </style>
        <div x-show="showCustomMail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div @click="showCustomMail=false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop
                x-init="$watch('showCustomMail', val => {
                    if (val) {
                        setTimeout(() => {
                            if (tinymce.get('custom-email-body')) tinymce.get('custom-email-body').remove();
                            tinymce.init({
                                selector: '#custom-email-body',
                                height: 150,
                                menubar: false,
                                plugins: 'lists link',
                                toolbar: 'undo redo | bold italic underline | bullist numlist | link | removeformat',
                                content_style: 'body { font-family: Arial, sans-serif; font-size: 13px; }'
                            });
                            ['#email-to-select','#email-cc-select','#email-bcc-select'].forEach(sel => {
                                const el = document.querySelector(sel);
                                if (el && !el._tomSelect) {
                                    new TomSelect(el, {
                                        create: true,
                                        plugins: ['remove_button'],
                                        createFilter: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                        render: { option_create: (data) => '<div class=create>Add <strong>' + data.input + '</strong></div>' }
                                    });
                                }
                            });
                        }, 150);
                    } else {
                        if (tinymce.get('custom-email-body')) tinymce.get('custom-email-body').remove();
                    }
                })">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Send Custom Email</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Compose and send to contacts, users, or any email</p>
                    </div>
                    <button @click="showCustomMail=false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="sendCustomMail()" class="p-6 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">To <span class="text-red-500">*</span></label>
                        <div class="email-ts-field">
                            <select id="email-to-select" multiple placeholder="Search contacts or type email...">
                                @foreach($contacts as $c)
                                <option value="{{ $c->email }}">{{ $c->name }} &lt;{{ $c->email }}&gt;</option>
                                @endforeach
                                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->email }}">{{ $u->name }} &lt;{{ $u->email }}&gt;</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3" x-data="{ showCcBcc: false }">
                        <div class="col-span-2 flex justify-end">
                            <button type="button" @click="showCcBcc = !showCcBcc"
                                class="text-xs text-teal-600 hover:text-teal-700 font-medium flex items-center gap-1">
                                <span x-text="showCcBcc ? 'Hide CC / BCC' : '+ Add CC / BCC'"></span>
                            </button>
                        </div>
                        <div x-show="showCcBcc" x-transition style="display:none;">
                            <label class="block text-xs font-medium text-slate-700 mb-1">CC</label>
                            <div class="email-ts-field">
                                <select id="email-cc-select" multiple placeholder="Add CC recipients...">
                                    @foreach($contacts as $c)
                                    <option value="{{ $c->email }}">{{ $c->name }} &lt;{{ $c->email }}&gt;</option>
                                    @endforeach
                                    @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                    <option value="{{ $u->email }}">{{ $u->name }} &lt;{{ $u->email }}&gt;</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div x-show="showCcBcc" x-transition style="display:none;">
                            <label class="block text-xs font-medium text-slate-700 mb-1">BCC</label>
                            <div class="email-ts-field">
                                <select id="email-bcc-select" multiple placeholder="Add BCC recipients...">
                                    @foreach($contacts as $c)
                                    <option value="{{ $c->email }}">{{ $c->name }} &lt;{{ $c->email }}&gt;</option>
                                    @endforeach
                                    @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                    <option value="{{ $u->email }}">{{ $u->name }} &lt;{{ $u->email }}&gt;</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Subject <span class="text-red-500">*</span></label>
                        <input type="text" x-model="customMailForm.subject" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none"
                            placeholder="Email subject...">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea id="custom-email-body" class="w-full"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                        <button type="button" @click="showCustomMail=false"
                            class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                        <button type="submit" :disabled="loading"
                            class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <span x-text="loading ? 'Sending...' : 'Send Email'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Send Report to Contact Modal --}}
        <div x-show="showContactReport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div @click="showContactReport=false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop
                x-init="$watch('showContactReport', val => { if(val) setTimeout(() => {
                    const initTs = (id, key) => {
                        const el = document.getElementById(id);
                        if(!el || el._tomSelect) return;
                        const ts = new TomSelect(el, { create: false });
                        ts.on('change', v => { this.contactReportForm[key] = v; });
                    };
                    initTs('cr-contact-select', 'contact_id');
                    initTs('cr-emp-select', 'employee_id');
                    initTs('cr-type-select', 'report_type');
                }, 100); })">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-bold text-slate-800">Send Report to Contact</h3>
                    <button @click="showContactReport=false" class="text-slate-400 hover:text-slate-600"><svg
                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg></button>
                </div>
                <form id="contact-report-modal" @submit.prevent="sendContactReport()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Contact <span
                                class="text-red-500">*</span></label>
                        <select id="cr-contact-select" x-model="contactReportForm.contact_id" required
                            @foreach ($contacts as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} &lt;{{ $c->email }}&gt;
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Employee <span
                                class="text-red-500">*</span></label>
                        <select id="cr-emp-select" x-model="contactReportForm.employee_id" required
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Report Type <span
                                class="text-red-500">*</span></label>
                        <select x-model="contactReportForm.report_type" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date From</label>
                            <input type="date" x-model="contactReportForm.date_from"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                            <input type="date" x-model="contactReportForm.date_to"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showContactReport=false"
                            class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                        <button type="submit" :disabled="loading"
                            class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            <span x-text="loading ? 'Sending...' : 'Send Report'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Contact Delete Modal --}}
        <div x-show="showContactDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div @click="showContactDelete=false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center" @click.stop>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg
                        class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg></div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Delete Contact?</h3>
                <p class="text-sm text-slate-500 mb-6">They will no longer receive any automated emails.</p>
                <div class="flex gap-3 justify-center">
                    <button @click="showContactDelete=false"
                        class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
                    <button @click="deleteContact()" :disabled="loading"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection
