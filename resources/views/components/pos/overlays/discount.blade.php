{{-- DiscountModal — percentage / fixed, whole bill or one line, reason required --}}
<x-pos.dialog name="discount" width="max-w-lg" title="Apply discount"
              subtitle="Reason is mandatory and is printed on the audit trail.">

    <div class="space-y-3 p-4">

        {{-- Mode --}}
        <div class="grid grid-cols-2 gap-1.5">
            @foreach ([['pct', 'Percentage'], ['amt', 'Fixed amount']] as [$k, $l])
                <button type="button" @click="discountDraft.mode = '{{ $k }}'"
                        :class="discountDraft.mode === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                        class="h-10 rounded-md border text-[12.5px] font-bold uppercase tracking-wide">{{ $l }}</button>
            @endforeach
        </div>

        {{-- Scope --}}
        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Apply to</p>
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" @click="discountDraft.scope = 'bill'; discountDraft.target = null"
                        :class="discountDraft.scope === 'bill' ? 'border-slate-900 bg-slate-50' : 'border-slate-300 bg-white'"
                        class="h-10 rounded-md border text-[12.5px] font-bold text-slate-800">Whole bill</button>
                <button type="button" @click="discountDraft.scope = 'item'"
                        :class="discountDraft.scope === 'item' ? 'border-slate-900 bg-slate-50' : 'border-slate-300 bg-white'"
                        class="h-10 rounded-md border text-[12.5px] font-bold text-slate-800">Selected item</button>
            </div>
            <select x-show="discountDraft.scope === 'item'" x-model="discountDraft.target"
                    class="mt-1.5 h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                <option :value="null">Choose a line…</option>
                <template x-for="l in billableLines" :key="l.uid">
                    <option :value="l.uid" x-text="l.qty + '× ' + l.name + ' — ' + money(lineTotal(l))"></option>
                </template>
            </select>
        </div>

        {{-- Value --}}
        <div class="grid grid-cols-[1fr_auto] gap-1.5">
            <div class="relative">
                <input x-model="discountDraft.value" data-autofocus type="number" min="0" inputmode="decimal" placeholder="0"
                       class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white px-3 pr-10 text-[19px] font-black text-slate-900 focus:border-slate-900 focus:outline-none" />
                <span class="pos-num absolute right-3 top-1/2 -translate-y-1/2 text-[15px] font-bold text-slate-400"
                      x-text="discountDraft.mode === 'pct' ? '%' : '₹'"></span>
            </div>
            <div class="flex gap-1">
                <template x-for="v in (discountDraft.mode === 'pct' ? [5, 10, 15, 20] : [50, 100, 200, 500])" :key="v">
                    <button type="button" @click="discountDraft.value = v"
                            class="pos-num h-12 w-12 rounded-md border border-slate-300 bg-white text-[12px] font-bold text-slate-700 hover:border-slate-900"
                            x-text="v"></button>
                </template>
            </div>
        </div>

        {{-- Reason --}}
        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason <span class="text-rose-600">*</span></p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="r in discountReasons" :key="r">
                    <button type="button" @click="discountDraft.reason = r"
                            :class="discountDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                            class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                </template>
            </div>
        </div>

        {{-- Live preview --}}
        <div class="rounded-md border border-slate-300 bg-slate-50 p-3">
            <dl class="space-y-1 text-[12px]">
                <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="pos-num font-semibold" x-text="money(subtotal)"></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Discount</dt><dd class="pos-num font-semibold text-emerald-700" x-text="'− ' + money(discountPreview)"></dd></div>
                <div class="flex justify-between border-t border-dashed border-slate-300 pt-1">
                    <dt class="font-bold text-slate-900">New taxable</dt>
                    <dd class="pos-num text-[15px] font-black text-slate-900" x-text="money(subtotal - discountPreview)"></dd>
                </div>
            </dl>

            <div x-show="discountNeedsApproval" class="mt-2 flex items-start gap-2 rounded border border-amber-400 bg-amber-50 p-2">
                <x-pos.icon name="lock" class="mt-px h-4 w-4 shrink-0 text-amber-700" />
                <p class="text-[11.5px] font-semibold leading-snug text-amber-900">
                    Manager approval required — <span class="pos-num" x-text="Math.round(discountPct) + '%'"></span>
                    exceeds the <span class="pos-num" x-text="operator.discountLimitPct + '%'"></span> limit for this role.
                </p>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex items-center gap-2">
            <button type="button" x-show="discount" @click="clearDiscount(); back()"
                    class="h-10 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-rose-600 hover:border-rose-500">Remove discount</button>
            <span class="flex-1"></span>
            <button type="button" @click="back()"
                    class="h-10 rounded-md border border-slate-300 bg-white px-4 text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="applyDiscount()"
                    :disabled="!(Number(discountDraft.value) > 0) || !discountDraft.reason"
                    class="h-10 rounded-md bg-slate-900 px-5 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-text="discountNeedsApproval ? 'Request approval' : 'Apply discount'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
