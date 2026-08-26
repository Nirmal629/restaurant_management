{{-- MergeTableModal — section 15. Only empty tables on the same floor are offered. --}}
<x-pos.dialog name="merge" width="max-w-lg" title="Merge Table">
    <template x-if="table(mergeDraft.primaryId)">
        <div class="p-4">
            <div class="rounded-md border border-slate-300 bg-slate-50 p-3">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Current table (primary)</p>
                <p class="pos-num text-[20px] font-black text-slate-900" x-text="mergeDraft.primaryId"></p>
                <p class="pos-num text-[11px] font-semibold text-slate-500" x-text="table(mergeDraft.primaryId).orderCode + ' · ' + (table(mergeDraft.primaryId).guests || 0) + ' guests'"></p>
            </div>

            <p class="mb-1.5 mt-3 text-[11.5px] font-semibold text-slate-500">Select another table to merge in:</p>
            <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));">
                <template x-for="t in mergeTargets" :key="t.id">
                    <button type="button" @click="mergeDraft.secondaryId = t.id"
                            :class="mergeDraft.secondaryId === t.id ? 'border-slate-900 bg-slate-900 text-white' : 'border-emerald-300 bg-emerald-50 text-slate-900 hover:border-emerald-600'"
                            class="rounded-md border-2 py-2">
                        <span class="pos-num block text-[15px] font-black" x-text="t.id"></span>
                        <span class="pos-num block text-[10px] font-semibold opacity-70" x-text="t.seats + ' seats'"></span>
                    </button>
                </template>
                <p x-show="!mergeTargets.length" class="col-span-full py-6 text-center text-[12px] font-semibold text-slate-400">No available tables on this floor.</p>
            </div>

            <div x-show="mergeDraft.secondaryId" class="mt-3 rounded-md border border-slate-300 bg-white p-3">
                <p class="text-[10px] font-black uppercase tracking-[0.08em] text-slate-500">Merged Group</p>
                <p class="pos-num mt-0.5 text-[18px] font-black text-slate-900" x-text="mergeDraft.primaryId + ' + ' + mergeDraft.secondaryId"></p>
                <div class="mt-1.5 flex gap-4 text-[12px]">
                    <span class="font-semibold text-slate-600">Combined Capacity
                        <span class="pos-num font-black text-slate-900" x-text="(table(mergeDraft.primaryId)?.seats || 0) + (table(mergeDraft.secondaryId)?.seats || 0)"></span>
                    </span>
                    <span class="font-semibold text-slate-600">Current Guests
                        <span class="pos-num font-black text-slate-900" x-text="table(mergeDraft.primaryId)?.guests || 0"></span>
                    </span>
                </div>
                <p class="mt-1 text-[10.5px] text-slate-400">Table <span class="pos-num font-bold text-slate-600" x-text="mergeDraft.primaryId"></span> stays primary — its order, waiter and KOT history carry the merge.</p>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmMerge()" :disabled="!mergeDraft.secondaryId || saving"
                    class="flex h-10 flex-1 items-center justify-center gap-2 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-show="saving && savingAction === 'merge'" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                <span x-text="saving && savingAction === 'merge' ? 'Merging...' : 'Merge Tables'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
