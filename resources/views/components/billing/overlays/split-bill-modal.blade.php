{{--
    SplitBillModal — Equal / By Item / By Amount / By Guest, each payable
    independently from within this drawer (PAY NEXT BILL). Sub-views
    (EqualSplitView / ItemSplitView / AmountSplitView / GuestSplitView) are
    mode-branches below rather than separate files, since they share this
    header/footer and the per-bill payment inline form.
--}}
<x-pos.dialog name="split" width="max-w-5xl" title="Split Bill" subtitle="Each item unit can only ever belong to one bill.">

    <div class="sticky top-0 z-10 flex flex-wrap items-center gap-1 border-b border-slate-200 bg-white px-3 py-2">
        @foreach ([['equal', 'Equal Split'], ['item', 'By Item'], ['amount', 'By Amount'], ['guest', 'By Guest']] as [$k, $l])
            <button type="button" @click="setSplitMode('{{ $k }}')"
                    :class="split.mode === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                    class="rounded-md border px-3 py-1.5 text-[12px] font-bold">{{ $l }}</button>
        @endforeach
        <span class="flex-1"></span>
        <span class="pos-num text-[12px] font-semibold text-slate-500">Grand Total <span class="text-[15px] font-black text-slate-900" x-text="money(grandTotal)"></span></span>
        <button type="button" @click="clearSplit(); back()" class="ml-2 text-[10.5px] font-bold text-rose-500 underline decoration-rose-300 underline-offset-2 hover:text-rose-700">Clear split</button>
    </div>

    <div class="p-3">

        {{-- EQUAL / GUEST ------------------------------------------------ --}}
        <div x-show="split.mode === 'equal' || split.mode === 'guest'" class="space-y-3">
            <div class="flex items-center gap-3 rounded-md border border-slate-300 bg-slate-50 p-3" x-show="split.mode === 'equal'">
                <span class="text-[12px] font-bold uppercase tracking-wide text-slate-600">Split Between</span>
                <x-pos.qty-control dec="setSplitWays(Math.max(2, split.ways - 1))" inc="setSplitWays(split.ways + 1)" value="split.ways" />
                <span class="flex-1"></span>
                <span class="text-[10.5px] font-semibold text-slate-400">Remainder is absorbed into the last bill</span>
            </div>

            <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
                <template x-for="b in split.bills" :key="b.idx">
                    <div class="rounded-md border-2 p-3" :class="b.status === 'paid' ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-white'">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-black uppercase tracking-[0.07em] text-slate-600" x-text="b.label"></p>
                            <span class="rounded px-1.5 py-px text-[9px] font-black uppercase tracking-wide" :class="b.status === 'paid' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600'" x-text="b.status"></span>
                        </div>
                        <p class="pos-num mt-0.5 text-[19px] font-black text-slate-900" x-text="money2(b.total)"></p>
                        <p class="mt-0.5 truncate text-[10.5px] font-semibold text-slate-400" x-text="b.customer"></p>

                        <template x-if="b.status !== 'paid' && splitPay.idx !== b.idx">
                            <button type="button" @click="openSplitPay(b)" class="mt-2 h-8 w-full rounded-md border border-slate-300 text-[11px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Pay This Bill</button>
                        </template>
                        <template x-if="splitPay.idx === b.idx"><x-billing.split-pay-inline /></template>
                    </div>
                </template>
            </div>
        </div>

        {{-- BY ITEM / BY GUEST-AS-ITEM ------------------------------------ --}}
        <div x-show="split.mode === 'item'" class="space-y-2">
            <div class="flex flex-wrap items-center gap-2 rounded-md border border-slate-300 bg-slate-50 p-2.5">
                <span class="text-[11px] font-black uppercase tracking-[0.08em] text-slate-600">Bills</span>
                <template x-for="b in split.bills" :key="b.idx">
                    <span class="pos-num rounded-md border border-slate-300 bg-white px-2.5 py-1 text-[11.5px] font-bold text-slate-700" x-text="b.label + ' · ' + money(b.total)"></span>
                </template>
                <button type="button" @click="addSplitBill()" class="flex h-7 items-center gap-1 rounded-md border border-dashed border-slate-400 px-2 text-[11px] font-bold text-slate-500 hover:border-slate-900 hover:text-slate-900">
                    <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Add Bill
                </button>
                <span class="flex-1"></span>
                <span x-show="splitUnassignedCount" class="pos-num rounded border border-amber-400 bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-900">
                    <span x-text="splitUnassignedCount"></span> unit(s) unassigned
                </span>
            </div>

            <div class="space-y-1.5">
                <template x-for="i in billableItems" :key="i.uid">
                    <div class="rounded-md border border-slate-200 bg-white p-2">
                        <div class="flex items-center gap-2">
                            <span class="pos-num shrink-0 rounded bg-slate-900 px-1.5 py-px text-[11px] font-bold text-white" x-text="i.qty + '×'"></span>
                            <span class="min-w-0 flex-1 truncate text-[12.5px] font-semibold text-slate-900" x-text="i.name"></span>
                            <span class="pos-num text-[12px] font-bold text-slate-900" x-text="money(netAmount(i))"></span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5 pl-6">
                            <template x-for="b in split.bills" :key="b.idx">
                                <div class="flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-1.5 py-1">
                                    <span class="text-[10px] font-bold text-slate-500" x-text="b.label"></span>
                                    <button type="button" @click="unassignUnit(i, b.idx)" :disabled="assignedQty(i.uid, b.idx) <= 0" class="grid h-5 w-5 place-items-center rounded bg-white text-slate-500 disabled:opacity-30"><x-pos.icon name="minus" class="h-3 w-3" stroke="2.6" /></button>
                                    <span class="pos-num w-4 text-center text-[11px] font-bold" x-text="assignedQty(i.uid, b.idx)"></span>
                                    <button type="button" @click="assignUnit(i, b.idx)" :disabled="unassignedQty(i) <= 0" class="grid h-5 w-5 place-items-center rounded bg-white text-slate-500 disabled:opacity-30"><x-pos.icon name="plus" class="h-3 w-3" stroke="2.6" /></button>
                                </div>
                            </template>
                            <span class="text-[10px] font-semibold text-slate-400" x-text="unassignedQty(i) + ' unassigned'"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                <template x-for="b in split.bills" :key="'pay-' + b.idx">
                    <div class="rounded-md border-2 p-2.5" :class="b.status === 'paid' ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-white'">
                        <div class="flex items-center justify-between">
                            <span class="text-[10.5px] font-black uppercase tracking-wide text-slate-600" x-text="b.label"></span>
                            <span class="pos-num text-[13px] font-black text-slate-900" x-text="money2(b.total)"></span>
                        </div>
                        <template x-if="b.status !== 'paid' && splitPay.idx !== b.idx">
                            <button type="button" @click="openSplitPay(b)" :disabled="b.total <= 0" class="mt-1.5 h-7 w-full rounded-md border border-slate-300 text-[10.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900 disabled:opacity-30">Pay</button>
                        </template>
                        <template x-if="splitPay.idx === b.idx"><x-billing.split-pay-inline /></template>
                        <span x-show="b.status === 'paid'" class="mt-1.5 block text-center text-[10px] font-black uppercase tracking-wide text-emerald-700">Paid</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- BY AMOUNT ------------------------------------------------- --}}
        <div x-show="split.mode === 'amount'" class="space-y-2">
            <p class="text-[11.5px] text-slate-500">Enter what each bill should carry — the remainder must reach zero.</p>
            <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                <template x-for="(b, idx) in split.bills" :key="idx">
                    <div class="rounded-md border-2 p-2.5" :class="b.status === 'paid' ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-white'">
                        <p class="text-[10.5px] font-black uppercase tracking-[0.08em] text-slate-500" x-text="b.label"></p>
                        <input type="number" min="0" :value="split.amounts[idx]" @input="setSplitAmount(idx, $event.target.value)"
                               class="pos-num mt-1 h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-right text-[16px] font-black focus:border-slate-900 focus:outline-none" />
                        <template x-if="b.status !== 'paid' && splitPay.idx !== b.idx">
                            <button type="button" @click="openSplitPay(b)" :disabled="b.total <= 0" class="mt-1.5 h-7 w-full rounded-md border border-slate-300 text-[10.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900 disabled:opacity-30">Pay</button>
                        </template>
                        <template x-if="splitPay.idx === idx"><x-billing.split-pay-inline /></template>
                        <span x-show="b.status === 'paid'" class="mt-1.5 block text-center text-[10px] font-black uppercase tracking-wide text-emerald-700">Paid</span>
                    </div>
                </template>
                <button type="button" @click="addSplitBill()" class="flex min-h-[96px] items-center justify-center gap-1.5 rounded-md border border-dashed border-slate-300 text-[12px] font-bold uppercase tracking-wide text-slate-500 hover:border-slate-900 hover:text-slate-900">
                    <x-pos.icon name="plus" class="h-4 w-4" stroke="2.4" /> Add Bill
                </button>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-300 bg-slate-50 px-3 py-2">
                <span class="text-[11.5px] font-black uppercase tracking-[0.08em] text-slate-600">Remaining</span>
                <span class="pos-num text-[17px] font-black" :class="splitAmountRemaining === 0 ? 'text-emerald-700' : 'text-rose-600'" x-text="money(splitAmountRemaining)"></span>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex items-center gap-2">
            <p class="flex-1 text-[11.5px] font-medium text-slate-500">
                <span x-show="!allSplitBillsPaid" x-text="'Table stays Partially Paid until every bill is settled.'"></span>
                <span x-show="allSplitBillsPaid" class="font-bold text-emerald-700">All split bills settled.</span>
            </p>
            <button type="button" x-show="nextPendingSplitBill && !splitPay.idx" @click="openSplitPay(nextPendingSplitBill)"
                    class="h-10 rounded-md border border-slate-300 bg-white px-4 text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Pay Next Bill</button>
            <button type="button" @click="back()"
                    class="h-10 rounded-md bg-slate-900 px-5 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
                <span x-text="allSplitBillsPaid ? 'Done' : 'Close'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
