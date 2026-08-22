{{-- REPRINT KOT confirmation — section 37. No print backend, just the confirm + conceptual reason. --}}
<x-pos.dialog name="reprint" width="max-w-sm" title="Reprint KOT">
    <div class="space-y-3 p-4">
        <p class="text-[13px] font-semibold text-slate-700">
            Reprint <span class="pos-num font-black text-slate-900" x-text="'KOT #' + reprintDraft.kot"></span>?
        </p>
        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason</p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="r in cancelReasons" :key="r">
                    <button type="button" @click="reprintDraft.reason = r"
                            :class="reprintDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                            class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                </template>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmReprint()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Reprint</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
