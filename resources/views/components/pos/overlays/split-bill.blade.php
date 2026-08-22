{{-- SplitBillModal — equal / by item / by amount / by guest --}}
<x-pos.dialog name="split" width="max-w-4xl" title="Split bill"
              subtitle="Each line belongs to exactly one bill, so nothing can be charged twice.">

    <div class="sticky top-0 z-10 flex items-center gap-1 border-b border-slate-200 bg-white px-3 py-2">
        @foreach ([['equal', 'Split equally'], ['item', 'Split by item'], ['amount', 'Split by amount'], ['guest', 'Split by guest']] as [$k, $l])
            <button type="button" @click="split.mode = '{{ $k }}'"
                    :class="split.mode === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                    class="rounded-md border px-3 py-1.5 text-[12px] font-bold">{{ $l }}</button>
        @endforeach
        <span class="flex-1"></span>
        <span class="pos-num text-[12px] font-semibold text-slate-500">Bill total <span class="text-[15px] font-black text-slate-900" x-text="money(total)"></span></span>
    </div>

    <div class="p-3">

        {{-- EQUAL / BY GUEST ------------------------------------------- --}}
        <div x-show="split.mode === 'equal' || split.mode === 'guest'" class="space-y-3">
            <div class="flex items-center gap-3 rounded-md border border-slate-300 bg-slate-50 p-3">
                <span class="text-[12px] font-bold uppercase tracking-wide text-slate-600"
                      x-text="split.mode === 'guest' ? 'Guests' : 'Ways'"></span>
                <x-pos.qty-control dec="split.ways = Math.max(2, split.ways - 1)" inc="split.ways++" value="split.ways" />
                <span class="flex-1"></span>
                <div class="text-right">
                    <p class="text-[10px] font-black uppercase tracking-[0.09em] text-slate-500">Each pays</p>
                    <p class="pos-num text-[24px] font-black leading-none text-slate-900" x-text="money(splitEach)"></p>
                </div>
            </div>

            <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                <template x-for="n in split.ways" :key="n">
                    <div class="rounded-md border border-slate-200 bg-white p-2.5">
                        <p class="text-[10.5px] font-black uppercase tracking-[0.08em] text-slate-500"
                           x-text="(split.mode === 'guest' ? 'Guest ' : 'Bill ') + n"></p>
                        <p class="pos-num mt-0.5 text-[17px] font-black text-slate-900"
                           x-text="money(n === split.ways ? total - splitEach * (split.ways - 1) : splitEach)"></p>
                        <button type="button" @click="notify('Bill ' + n + ' ready for payment')"
                                class="mt-1.5 h-8 w-full rounded-md border border-slate-300 text-[11px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Take payment</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- BY ITEM ---------------------------------------------------- --}}
        <div x-show="split.mode === 'item'" class="space-y-2">
            <div class="flex flex-wrap items-center gap-2 rounded-md border border-slate-300 bg-slate-50 p-2.5">
                <span class="text-[11px] font-black uppercase tracking-[0.08em] text-slate-600">Bills</span>
                @foreach ([2, 3, 4] as $n)
                    <button type="button" @click="setSplitBills({{ $n }})"
                            :class="split.bills === {{ $n }} ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                            class="pos-num h-8 w-8 rounded-md border text-[12px] font-bold">{{ $n }}</button>
                @endforeach

                <span class="mx-1 h-6 w-px bg-slate-300"></span>

                <span class="text-[11px] font-black uppercase tracking-[0.08em] text-slate-600">Assigning to</span>
                <template x-for="n in split.bills" :key="n">
                    <button type="button" @click="split.activeBill = n"
                            :class="split.activeBill === n ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700'"
                            class="flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-[11.5px] font-bold">
                        <span x-text="'Bill ' + n"></span>
                        <span class="pos-num rounded px-1 text-[10px]"
                              :class="split.activeBill === n ? 'bg-white/20' : 'bg-slate-100 text-slate-500'"
                              x-text="money(splitBillTotal(n))"></span>
                    </button>
                </template>

                <span class="flex-1"></span>
                <span x-show="splitUnassigned.length"
                      class="pos-num rounded border border-amber-400 bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-900">
                    <span x-text="splitUnassigned.length"></span> line(s) unassigned
                </span>
            </div>

            <div class="space-y-1">
                <template x-for="l in billableLines" :key="l.uid">
                    <button type="button" @click="assignLine(l.uid)"
                            :class="split.assign[l.uid] ? 'border-slate-900 bg-slate-50' : 'border-dashed border-slate-300 bg-white'"
                            class="flex w-full items-center gap-2 rounded-md border px-2.5 py-2 text-left hover:border-slate-900">
                        <span class="pos-num shrink-0 rounded bg-slate-900 px-1.5 py-px text-[11px] font-bold text-white" x-text="l.qty + '×'"></span>
                        <span class="min-w-0 flex-1 truncate text-[12.5px] font-semibold text-slate-900" x-text="l.name"></span>
                        <span class="pos-num text-[12.5px] font-bold text-slate-900" x-text="money(lineTotal(l))"></span>
                        <span :class="split.assign[l.uid] ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 text-slate-400'"
                              class="pos-num w-16 shrink-0 rounded border px-1.5 py-0.5 text-center text-[10.5px] font-bold uppercase tracking-wide"
                              x-text="split.assign[l.uid] ? 'Bill ' + split.assign[l.uid] : 'None'"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- BY AMOUNT -------------------------------------------------- --}}
        <div x-show="split.mode === 'amount'" class="space-y-2">
            <p class="text-[11.5px] text-slate-500">Enter what each bill should carry. The remainder is shown live and must reach zero.</p>
            <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));">
                <template x-for="(a, i) in split.amounts" :key="i">
                    <div class="rounded-md border border-slate-200 bg-white p-2.5">
                        <p class="text-[10.5px] font-black uppercase tracking-[0.08em] text-slate-500" x-text="'Bill ' + (i + 1)"></p>
                        <input type="number" min="0" x-model.number="split.amounts[i]"
                               class="pos-num mt-1 h-11 w-full rounded-md border border-slate-300 bg-white px-2.5 text-right text-[18px] font-black focus:border-slate-900 focus:outline-none" />
                    </div>
                </template>
                <button type="button" @click="split.amounts.push(0)"
                        class="flex min-h-[86px] items-center justify-center gap-1.5 rounded-md border border-dashed border-slate-300 text-[12px] font-bold uppercase tracking-wide text-slate-500 hover:border-slate-900 hover:text-slate-900">
                    <x-pos.icon name="plus" class="h-4 w-4" stroke="2.4" /> Add bill
                </button>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-300 bg-slate-50 px-3 py-2">
                <span class="text-[11.5px] font-black uppercase tracking-[0.08em] text-slate-600">Unallocated</span>
                <span class="pos-num text-[17px] font-black"
                      :class="(total - split.amounts.reduce((s, v) => s + (Number(v) || 0), 0)) === 0 ? 'text-emerald-700' : 'text-rose-600'"
                      x-text="money(total - split.amounts.reduce((s, v) => s + (Number(v) || 0), 0))"></span>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex items-center gap-2">
            <p class="flex-1 text-[11.5px] font-medium text-slate-500">Each split bill is settled separately in the payment drawer.</p>
            <button type="button" @click="back()"
                    class="h-10 rounded-md border border-slate-300 bg-white px-4 text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="notify('Split applied — settle each bill in turn'); back()"
                    :disabled="split.mode === 'item' && splitUnassigned.length"
                    class="h-10 rounded-md bg-slate-900 px-5 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Apply split</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
