{{-- Billing state actions — section 20. Table cannot become AVAILABLE from here directly. --}}
<x-pos.dialog name="billing" width="max-w-sm" title="Table Billing" :subtitle="null">
    <template x-if="activeCard">
        <div class="p-4">
            <div class="mb-3 flex items-center gap-2">
                <span class="pos-num text-[18px] font-black text-slate-900" x-text="activeCard.label"></span>
                <x-tables.status-badge expr="'billing'" />
            </div>

            <div class="rounded-md border border-amber-300 bg-amber-50 p-3 text-center">
                <p class="text-[10px] font-black uppercase tracking-wide text-amber-700">Bill</p>
                <p class="pos-num text-[26px] font-black leading-tight text-amber-900" x-text="money(activeCard.amount)"></p>
                <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wide text-amber-700">Payment pending</p>
            </div>

            <p class="mt-2 text-[10.5px] leading-snug text-slate-500">
                This table stays in <span class="font-bold text-slate-700">Bill Ready</span> until payment settles
                in POS — it will not free up on its own.
            </p>
        </div>
    </template>

    <x-slot:footer>
        <div class="space-y-1.5">
            <button type="button" @click="goToPos(activeCard)"
                    class="flex h-11 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">
                <x-pos.icon name="cash" class="h-4 w-4" /> Take Payment
            </button>
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" @click="goToPos(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Open Bill</button>
                <button type="button" @click="printBill(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Print Bill</button>
            </div>
            <button type="button" @click="cancelBill(activeCard)"
                    class="h-9 w-full rounded-md border border-rose-300 bg-white text-[11.5px] font-bold text-rose-600 hover:border-rose-500 hover:bg-rose-50">Cancel Bill</button>
            <button type="button" @click="markSettled(activeCard)"
                    class="h-9 w-full rounded-md border border-dashed border-slate-300 text-[10.5px] font-bold uppercase tracking-wide text-slate-400 hover:border-slate-900 hover:text-slate-700">
                Preview only — mark settled &amp; free table
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
