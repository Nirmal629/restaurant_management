{{-- MixedPaymentRows — every tender captured so far; removable while due > 0 conceptually allows correction. --}}
<div x-show="payments.length">
    <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-500">Payments</p>
    <div class="space-y-1.5">
        <template x-for="(p, idx) in payments" :key="idx">
            <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-2.5 py-2">
                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10.5px] font-black uppercase tracking-wide text-slate-700" x-text="p.label"></span>
                <span class="pos-num min-w-0 flex-1 truncate text-[11px] text-slate-500" x-text="p.reference || p.at"></span>
                <span class="pos-num text-[13px] font-bold text-slate-900" x-text="money(p.amount)"></span>
                <button type="button" @click="removePayment(idx)" class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-400 hover:border-rose-500 hover:text-rose-600">
                    <x-pos.icon name="trash" class="h-3.5 w-3.5" />
                </button>
            </div>
        </template>
    </div>
</div>
