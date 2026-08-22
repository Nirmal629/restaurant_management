{{-- OrderTotals — pos-dock, so it survives any cart length --}}
<div class="pos-dock border-t border-slate-200 bg-white px-3 py-2">

    <dl class="space-y-[3px] text-[11.5px]">
        <div class="flex items-baseline justify-between">
            <dt class="font-medium text-slate-500">
                Subtotal <span class="pos-num text-slate-400">(<span x-text="itemCount"></span> items)</span>
            </dt>
            <dd class="pos-num font-semibold text-slate-800" x-text="money(subtotal)"></dd>
        </div>

        <div class="flex items-baseline justify-between">
            <dt>
                <button type="button" @click="openDiscount()"
                        class="flex items-center gap-1 font-medium text-slate-500 underline decoration-slate-300 underline-offset-2 hover:text-slate-900 hover:decoration-slate-900">
                    <span x-text="discount ? 'Discount · ' + (discount.mode === 'pct' ? discount.value + '%' : 'flat') : 'Add discount'"></span>
                    <span x-show="discount?.approvedBy" class="rounded bg-amber-100 px-1 text-[9px] font-bold uppercase text-amber-800">Approved</span>
                </button>
            </dt>
            <dd class="pos-num font-semibold" :class="discountAmount ? 'text-emerald-700' : 'text-slate-400'"
                x-text="discountAmount ? '− ' + money(discountAmount) : money(0)"></dd>
        </div>

        <div class="flex items-baseline justify-between">
            <dt class="font-medium text-slate-500">
                <span x-text="charges.taxLabel"></span>
                <span class="pos-num text-slate-400" x-text="'(' + (charges.taxRate * 100) + '%)'"></span>
            </dt>
            <dd class="pos-num font-semibold text-slate-800" x-text="money(taxAmount)"></dd>
        </div>

        <div class="flex items-baseline justify-between" x-show="charges.serviceEnabled">
            <dt class="font-medium text-slate-500">
                <span x-text="charges.serviceLabel"></span>
                <span class="pos-num text-slate-400" x-text="'(' + (charges.serviceRate * 100) + '%)'"></span>
            </dt>
            <dd class="pos-num font-semibold text-slate-800" x-text="money(serviceAmount)"></dd>
        </div>

        <div class="flex items-baseline justify-between" x-show="Math.abs(roundOff) >= 0.005">
            <dt class="font-medium text-slate-500">Round off</dt>
            <dd class="pos-num font-semibold text-slate-800" x-text="(roundOff >= 0 ? '+ ' : '− ') + money2(Math.abs(roundOff))"></dd>
        </div>
    </dl>

    <div class="mt-1.5 flex items-baseline justify-between border-t-2 border-dashed border-slate-300 pt-1.5">
        <span class="text-[12px] font-black uppercase tracking-[0.08em] text-slate-900">Total</span>
        <span class="pos-num text-[22px] font-black leading-none tracking-tight text-slate-900" x-text="money(total)"></span>
    </div>

    {{-- Partial settlement state, only when it exists --}}
    <div x-show="payments.length" class="mt-1 flex items-baseline justify-between text-[11px]">
        <span class="font-semibold text-slate-500">Paid <span class="pos-num" x-text="money(paid)"></span></span>
        <span class="pos-num font-bold" :class="due ? 'text-rose-600' : 'text-emerald-700'"
              x-text="due ? 'Due ' + money(due) : 'Settled'"></span>
    </div>
</div>
