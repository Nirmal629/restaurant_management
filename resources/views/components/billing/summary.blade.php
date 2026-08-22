{{--
    BillSummary — DiscountSummary + TaxBreakdown + ServiceChargeRow +
    GrandTotalDisplay. $attributes is merged onto the root so the caller can
    bind a reactive class (e.g. the tab-switcher's x-bind:class) directly onto
    this flex item — wrapping it in a plain <div> instead breaks the
    percentage-width flex sizing below 1280px and even above it.
--}}
<section {{ $attributes->merge(['class' => 'bil-summary-col border-r border-slate-200 bg-white']) }}>
    <div class="pos-dock border-b border-slate-200 px-3 py-2">
        <h2 class="text-[11.5px] font-black uppercase tracking-[0.07em] text-slate-800">Bill Summary</h2>
    </div>

    <div class="pos-scroll px-3 py-2.5">
        <dl class="space-y-1.5 text-[11.5px]">
            <div class="flex items-baseline justify-between">
                <dt class="font-medium text-slate-500">Subtotal</dt>
                <dd class="pos-num font-semibold text-slate-800" x-text="money(subtotal)"></dd>
            </div>

            <div class="flex items-baseline justify-between" x-show="itemDiscountTotal">
                <dt class="font-medium text-slate-500">Item Discounts</dt>
                <dd class="pos-num font-semibold text-amber-700" x-text="'− ' + money(itemDiscountTotal)"></dd>
            </div>

            <div class="flex items-baseline justify-between" x-show="complimentaryTotal">
                <dt class="font-medium text-slate-500">Complimentary</dt>
                <dd class="pos-num font-semibold text-violet-700" x-text="'− ' + money(complimentaryTotal)"></dd>
            </div>

            <div class="flex items-baseline justify-between">
                <dt>
                    <button type="button" @click="openDiscount()" class="flex items-center gap-1 font-medium text-slate-500 underline decoration-slate-300 underline-offset-2 hover:text-slate-900 hover:decoration-slate-900">
                        <span x-text="billDiscount ? 'Bill Discount · ' + (billDiscount.mode === 'pct' ? billDiscount.value + '%' : 'flat') : 'Apply Discount'"></span>
                        <span x-show="billDiscount?.approvedBy" class="rounded bg-amber-100 px-1 text-[9px] font-bold uppercase text-amber-800">Approved</span>
                    </button>
                </dt>
                <dd class="pos-num font-semibold" :class="billDiscountAmount ? 'text-amber-700' : 'text-slate-400'" x-text="billDiscountAmount ? '− ' + money(billDiscountAmount) : money(0)"></dd>
            </div>

            <div class="flex items-baseline justify-between" x-show="loyaltyRedeemed">
                <dt class="font-medium text-slate-500">Loyalty Redeemed <span class="pos-num text-slate-400" x-text="'(' + loyaltyRedeemed?.points + ' pts)'"></span></dt>
                <dd class="pos-num font-semibold text-emerald-700" x-text="'− ' + money(loyaltyAmount)"></dd>
            </div>

            <div class="flex items-baseline justify-between" x-show="coupon">
                <dt class="font-medium text-slate-500">Coupon <span class="pos-num text-slate-400" x-text="coupon?.code"></span></dt>
                <dd class="pos-num font-semibold text-emerald-700" x-text="'− ' + money(couponAmount)"></dd>
            </div>

            <div class="flex items-baseline justify-between border-t border-dashed border-slate-300 pt-1.5">
                <dt class="font-bold text-slate-700">Taxable Amount</dt>
                <dd class="pos-num font-bold text-slate-900" x-text="money(taxableAmount)"></dd>
            </div>

            <x-billing.tax-breakdown />
            <x-billing.service-charge-row />

            <div class="flex items-baseline justify-between" x-show="Math.abs(roundOff) >= 0.005">
                <dt class="font-medium text-slate-500">Round Off</dt>
                <dd class="pos-num font-semibold text-slate-800" x-text="(roundOff >= 0 ? '+ ' : '− ') + money2(Math.abs(roundOff))"></dd>
            </div>
        </dl>

        <div class="mt-2.5">
            <x-billing.grand-total-display />
        </div>

        {{-- Quick links kept out of MORE for speed --}}
        <div class="mt-3 grid grid-cols-2 gap-1.5">
            <button type="button" @click="openLoyalty()" x-show="customer.loyalty.points > 0"
                    class="col-span-2 flex h-8 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold text-slate-700 hover:border-slate-900">
                <x-pos.icon name="star" class="h-3.5 w-3.5" /> Loyalty: <span class="pos-num" x-text="customer.loyalty.points + ' pts'"></span>
            </button>
            <button type="button" @click="openCoupon()"
                    class="flex h-8 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold text-slate-700 hover:border-slate-900">
                <x-pos.icon name="tag" class="h-3.5 w-3.5" /> Coupon
            </button>
            <button type="button" @click="open('preview')"
                    class="flex h-8 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold text-slate-700 hover:border-slate-900">
                <x-pos.icon name="receipt" class="h-3.5 w-3.5" /> Preview
            </button>
        </div>
    </div>
</section>
