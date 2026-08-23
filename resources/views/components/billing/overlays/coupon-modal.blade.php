{{-- Apply Coupon --}}
<x-pos.dialog name="coupon" width="max-w-md" title="Apply Coupon">
    <div class="space-y-3 p-4">
        <div x-show="coupon" class="flex items-center justify-between rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2">
            <span class="text-[12px] font-bold text-emerald-800" x-text="coupon?.code + ' applied'"></span>
            <button type="button" @click="clearCoupon()" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="text-[10.5px] font-bold text-rose-600 underline decoration-rose-300 underline-offset-2 disabled:cursor-wait disabled:opacity-50">Remove</button>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Available Coupons</p>
            <div class="max-h-64 space-y-1.5 overflow-y-auto">
                <template x-for="c in availableCoupons" :key="c.id">
                    <button type="button" @click="selectCoupon(c)"
                            :class="couponDraft.code === c.code ? 'border-slate-900 bg-slate-50 ring-1 ring-slate-900' : 'border-slate-200 bg-white'"
                            class="w-full rounded-md border p-3 text-left hover:border-slate-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="pos-num text-[13px] font-black text-slate-900" x-text="c.code"></p>
                                <p class="truncate text-[12px] font-bold text-slate-700" x-text="c.name"></p>
                            </div>
                            <p class="pos-num shrink-0 text-[15px] font-black text-emerald-700" x-text="'-' + money(c.amount)"></p>
                        </div>
                        <p class="mt-1 text-[10.5px] font-semibold text-slate-500">
                            <span x-text="c.type === 'percent' ? c.value + '% off' : money(c.value) + ' off'"></span>
                            <span x-show="c.minBillAmount"> · Min <span x-text="money(c.minBillAmount)"></span></span>
                            <span x-show="c.maxDiscountAmount"> · Cap <span x-text="money(c.maxDiscountAmount)"></span></span>
                            <span x-show="c.expiresAt"> · Expires <span x-text="formatDate(c.expiresAt)"></span></span>
                        </p>
                    </button>
                </template>
                <p x-show="!availableCoupons.length" class="rounded-md border border-slate-200 bg-slate-50 p-4 text-center text-[12px] font-semibold text-slate-400">No active coupons are eligible for this bill.</p>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Manual Code</label>
            <input x-model="couponDraft.code" data-autofocus placeholder="Coupon code" @keydown.enter="applyCoupon()"
                   class="pos-num h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-center text-[16px] font-black uppercase tracking-widest focus:border-slate-900 focus:outline-none" />
        </div>

        <p class="text-[10.5px] text-slate-400">Coupons are validated against active dates, bill minimums, usage limits, and walk-in eligibility.</p>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="applyCoupon()" :disabled="saving || !couponDraft.code.trim()" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Apply</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
