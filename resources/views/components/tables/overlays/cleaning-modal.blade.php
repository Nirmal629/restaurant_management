{{-- Cleaning state — section 21. --}}
<x-pos.dialog name="cleaning" width="max-w-xs" title="Table Cleaning" :subtitle="null">
    <template x-if="activeCard">
        <div class="p-4 text-center">
            <span class="pos-num text-[20px] font-black text-slate-900" x-text="activeCard.label"></span>
            <div class="mt-2">
                <x-tables.status-badge expr="'cleaning'" />
            </div>
            <p class="pos-num mt-2 text-[13px] font-semibold"
               :class="Number.isFinite(activeCard.since) && activeCard.since >= config.cleaningWarnMinutes ? 'text-amber-600' : 'text-slate-500'"
               x-text="cleaningLabel(activeCard)"></p>
        </div>
    </template>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Close</button>
            <button type="button" @click="markAvailable(activeCard)" :disabled="saving"
                    class="flex h-10 flex-1 items-center justify-center gap-2 rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500 disabled:cursor-wait disabled:bg-slate-300">
                <span x-show="saving && savingAction === 'available'" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                <span x-text="saving && savingAction === 'available' ? 'Marking...' : 'Mark Available'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
