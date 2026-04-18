<div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
    <div @click="showDelete=false" class="absolute inset-0 bg-slate-900/50"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center" @click.stop>
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Delete {{ $label }}?</h3>
        <p class="text-sm text-slate-500 mb-6">This action cannot be undone.</p>
        <div class="flex gap-3 justify-center">
            <button @click="showDelete=false" class="px-4 py-2 text-sm border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">Cancel</button>
            <button @click="deleteItem()" :disabled="loading" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">Delete</button>
        </div>
    </div>
</div>

