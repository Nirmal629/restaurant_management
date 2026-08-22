{{-- TransferTableModal — section 14. Works for an occupied order or a reservation link. --}}
<x-pos.dialog name="transfer" width="max-w-lg" title="Transfer Table">
    <template x-if="table(transferDraft.fromId)">
        <div class="p-4">
            <div class="flex items-center gap-2 rounded-md border border-slate-300 bg-slate-50 p-3">
                <div class="flex-1 text-center">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Current</p>
                    <p class="pos-num text-[20px] font-black text-slate-900" x-text="transferDraft.fromId"></p>
                    <p class="pos-num text-[10.5px] font-semibold text-slate-500"
                       x-text="table(transferDraft.fromId).status === 'reserved' ? reservationFor(transferDraft.fromId)?.customer : table(transferDraft.fromId).orderCode"></p>
                </div>
                <x-pos.icon name="chevron-right" class="h-5 w-5 shrink-0 text-slate-400" />
                <div class="flex-1 text-center">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">To</p>
                    <p class="pos-num text-[20px] font-black" :class="transferDraft.toId ? 'text-slate-900' : 'text-slate-300'" x-text="transferDraft.toId || '—'"></p>
                    <p class="text-[10.5px] font-semibold text-slate-400" x-text="table(transferDraft.fromId).guests ? table(transferDraft.fromId).guests + ' guests' : ''"></p>
                </div>
            </div>

            <p class="mb-1.5 mt-3 text-[11.5px] font-semibold text-slate-500">Only tables with enough free seats are shown:</p>
            <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));">
                <template x-for="t in transferTargets" :key="t.id">
                    <button type="button" @click="transferDraft.toId = t.id"
                            :class="transferDraft.toId === t.id ? 'border-slate-900 bg-slate-900 text-white' : 'border-emerald-300 bg-emerald-50 text-slate-900 hover:border-emerald-600'"
                            class="rounded-md border-2 py-2">
                        <span class="pos-num block text-[15px] font-black" x-text="t.id"></span>
                        <span class="pos-num block text-[10px] font-semibold opacity-70" x-text="t.seats + ' seats'"></span>
                    </button>
                </template>
                <p x-show="!transferTargets.length" class="col-span-full py-6 text-center text-[12px] font-semibold text-slate-400">No eligible tables free right now.</p>
            </div>

            <div x-show="transferDraft.toId" class="mt-3 rounded-md border border-amber-300 bg-amber-50 p-2.5 text-[12px] font-semibold text-amber-900">
                Transfer <span x-text="table(transferDraft.fromId).status === 'reserved' ? 'this reservation' : 'the current order'"></span>
                from <span class="pos-num font-black" x-text="transferDraft.fromId"></span> to
                <span class="pos-num font-black" x-text="transferDraft.toId"></span>?
            </div>
        </div>
    </template>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmTransfer()" :disabled="!transferDraft.toId"
                    class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Transfer</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
