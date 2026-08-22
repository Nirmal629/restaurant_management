{{-- Cancel Item — from the bill item's ⋮ menu. Excludes it from the payable total, never deletes it. --}}
<x-pos.dialog name="cancelItem" width="max-w-sm" title="Cancel Item">
    <template x-if="items.find(i => i.uid === cancelDraft.uid)">
        <div class="space-y-3 p-4">
            <div class="flex items-center gap-2 rounded-md border border-slate-300 bg-slate-50 p-2.5">
                <span class="pos-num shrink-0 rounded bg-slate-900 px-1.5 py-px text-[11px] font-bold text-white" x-text="items.find(i => i.uid === cancelDraft.uid).qty + '×'"></span>
                <span class="min-w-0 flex-1 truncate text-[13px] font-bold text-slate-900" x-text="items.find(i => i.uid === cancelDraft.uid).name"></span>
                <span class="pos-num text-[13px] font-bold text-slate-900" x-text="money(items.find(i => i.uid === cancelDraft.uid).amount)"></span>
            </div>
            <div>
                <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason <span class="text-rose-600">*</span></p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="r in cancelReasons" :key="r">
                        <button type="button" @click="cancelDraft.reason = r"
                                :class="cancelDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                                class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                    </template>
                </div>
            </div>
            <p class="rounded border border-slate-200 bg-slate-50 p-2 text-[11px] leading-snug text-slate-500">
                The line stays on the bill marked <span class="font-bold">Cancelled</span> for the audit trail and is
                excluded from the payable total.
            </p>
        </div>
    </template>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Keep Item</button>
            <button type="button" @click="confirmCancelItem()" :disabled="!cancelDraft.reason"
                    class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Cancel Item</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
