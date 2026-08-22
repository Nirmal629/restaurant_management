{{-- Apply Coupon — section 38. No coupon engine; only WELCOME10 / FESTIVE15 validate in this demo. --}}
<x-pos.dialog name="coupon" width="max-w-sm" title="Apply Coupon">
    <div class="space-y-3 p-4">
        <input x-model="couponDraft.code" data-autofocus placeholder="Coupon code" @keydown.enter="applyCoupon()"
               class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white px-3 text-center text-[18px] font-black uppercase tracking-widest focus:border-slate-900 focus:outline-none" />

        <div x-show="coupon" class="flex items-center justify-between rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2">
            <span class="text-[12px] font-bold text-emerald-800" x-text="coupon?.code + ' — ' + coupon?.pct + '% applied'"></span>
            <button type="button" @click="clearCoupon()" class="text-[10.5px] font-bold text-rose-600 underline decoration-rose-300 underline-offset-2">Remove</button>
        </div>

        <p class="text-[10.5px] text-slate-400">Try <span class="pos-num font-bold text-slate-600">WELCOME10</span> or <span class="pos-num font-bold text-slate-600">FESTIVE15</span> — any other code shows as invalid/expired.</p>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="applyCoupon()" :disabled="!couponDraft.code.trim()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Apply</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
