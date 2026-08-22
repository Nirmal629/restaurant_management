{{-- RefundModal — reachable only via MORE, never a primary payment button. Always manager-approval gated. --}}
<x-pos.dialog name="refund" width="max-w-lg" title="Refund" subtitle="Reachable via More → Refund only — never a primary settlement action.">
    <div class="space-y-3 p-4">
        <div class="grid grid-cols-2 gap-2">
            <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Invoice</p>
                <p class="pos-num text-[14px] font-black text-slate-900" x-text="invoice.code"></p>
            </div>
            <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Original Payment</p>
                <p class="pos-num text-[14px] font-black text-slate-900" x-text="money(paidTotal)"></p>
            </div>
            <div class="col-span-2 rounded-md border border-emerald-200 bg-emerald-50 p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-emerald-700">Refundable Amount</p>
                <p class="pos-num text-[18px] font-black text-emerald-800" x-text="money(paidTotal - refundedTotal)"></p>
            </div>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Refund Type</p>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach (['full' => 'Full Refund', 'partial' => 'Partial Refund', 'item' => 'Item Refund'] as $k => $l)
                    <button type="button" @click="refundDraft.mode = '{{ $k }}'"
                            :class="refundDraft.mode === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700'"
                            class="h-10 rounded-md border text-[11.5px] font-bold">{{ $l }}</button>
                @endforeach
            </div>
        </div>

        <div x-show="refundDraft.mode === 'partial'">
            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Refund Amount</label>
            <input x-model="refundDraft.amount" type="number" min="0" class="pos-num h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-right text-[17px] font-black focus:border-slate-900 focus:outline-none" />
        </div>

        <div x-show="refundDraft.mode === 'item'">
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Select items</p>
            <div class="max-h-40 space-y-1 overflow-y-auto">
                <template x-for="i in billableItems" :key="i.uid">
                    <button type="button" @click="toggleRefundItem(i.uid)"
                            :class="refundDraft.items.includes(i.uid) ? 'border-slate-900 bg-slate-50' : 'border-slate-200 bg-white'"
                            class="flex w-full items-center gap-2 rounded-md border px-2.5 py-1.5 text-left">
                        <span class="pos-num text-[11px] font-bold text-slate-900" x-text="i.qty + '×'"></span>
                        <span class="min-w-0 flex-1 truncate text-[12px] font-semibold text-slate-800" x-text="i.name"></span>
                        <span class="pos-num text-[12px] font-bold text-slate-900" x-text="money(netAmount(i))"></span>
                    </button>
                </template>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Refund To</label>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="m in ['cash', 'upi', 'credit', 'debit']" :key="m">
                    <button type="button" @click="refundDraft.method = m" :class="refundDraft.method === m ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                            class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold capitalize" x-text="m"></button>
                </template>
            </div>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason <span class="text-rose-600">*</span></p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="r in refundReasons" :key="r">
                    <button type="button" @click="refundDraft.reason = r" :class="refundDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                            class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                </template>
            </div>
        </div>

        <div class="rounded-md border border-rose-200 bg-rose-50 p-2.5 text-center">
            <p class="text-[10px] font-black uppercase tracking-wide text-rose-700">Refund Amount</p>
            <p class="pos-num text-[20px] font-black text-rose-800" x-text="money(refundPreviewAmount)"></p>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmRefund()" :disabled="!refundDraft.reason || refundPreviewAmount <= 0"
                    class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Request Approval</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
