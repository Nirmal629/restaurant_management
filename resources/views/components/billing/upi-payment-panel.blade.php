{{-- UpiPaymentPanel — design-only status states; no gateway. --}}
<div x-show="payDraft.method === 'upi'" class="space-y-2">
    <div>
        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.08em] text-slate-500">Amount</label>
        <div class="relative">
            <span class="pos-num absolute left-3 top-1/2 -translate-y-1/2 text-[17px] font-bold text-slate-400">₹</span>
            <input x-ref="payAmountUpi" x-model="payDraft.amount" type="number" min="0" inputmode="decimal"
                   class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-right text-[22px] font-black tracking-tight text-slate-900 focus:border-slate-900 focus:outline-none" />
        </div>
    </div>

    <div class="grid place-items-center rounded-md border border-dashed border-slate-300 bg-slate-50 py-4">
        <div class="h-20 w-20 rounded border border-slate-300 bg-white p-1.5" style="display:grid;grid-template-columns:repeat(6,1fr);gap:2px;">
            <template x-for="n in 36" :key="n"><span class="aspect-square rounded-[1px]" :class="(n * 7) % 5 === 0 ? 'bg-slate-900' : 'bg-transparent'"></span></template>
        </div>
        <p class="mt-1.5 text-[9.5px] font-semibold uppercase tracking-wide text-slate-400">QR placeholder — not scannable</p>
    </div>

    <input x-model="payDraft.reference" placeholder="Reference number (optional)"
           class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12px] font-medium focus:border-slate-900 focus:outline-none" />

    <div class="flex items-center gap-1.5">
        <span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Status</span>
        <span class="rounded px-1.5 py-0.5 text-[9.5px] font-black uppercase tracking-wide"
              :class="{ waiting: 'bg-slate-100 text-slate-600', paid: 'bg-emerald-100 text-emerald-800', failed: 'bg-rose-100 text-rose-700' }[upiStatus]"
              x-text="upiStatus"></span>
        <span class="flex-1"></span>
        <button type="button" @click="markUpiReceived()" :disabled="upiStatus === 'paid'"
                class="h-8 rounded-md bg-emerald-600 px-2.5 text-[10.5px] font-bold uppercase tracking-wide text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-300">
            Mark Received
        </button>
    </div>
</div>
