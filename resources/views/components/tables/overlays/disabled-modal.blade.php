{{-- Disabled table — tap-through per section 8 ("always display status"). --}}
<x-pos.dialog name="disabled" width="max-w-xs" title="Table Disabled" :subtitle="null">
    <template x-if="activeCard">
        <div class="p-4 text-center">
            <span class="pos-num text-[20px] font-black text-slate-900" x-text="activeCard.label"></span>
            <div class="mt-2"><x-tables.status-badge expr="'disabled'" /></div>
            <p class="mt-2 text-[12px] font-medium text-slate-500" x-text="activeCard.note || 'Not currently in service.'"></p>
        </div>
    </template>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="openEditTable(activeCard)"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Edit Table</button>
            <button type="button" @click="markAvailable(activeCard)"
                    class="h-10 flex-1 rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">Mark Available</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
