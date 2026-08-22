{{-- Shared amount+reference form for Wallet / Bank Transfer / Other. --}}
<div x-show="['wallet', 'bank', 'other'].includes(payDraft.method)" class="space-y-2">
    <div>
        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.08em] text-slate-500" x-text="paymentMethods.find(m => m.key === payDraft.method)?.label + ' Amount'"></label>
        <div class="relative">
            <span class="pos-num absolute left-3 top-1/2 -translate-y-1/2 text-[17px] font-bold text-slate-400">₹</span>
            <input x-ref="payAmount" x-model="payDraft.amount" type="number" min="0" inputmode="decimal"
                   class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-right text-[22px] font-black tracking-tight text-slate-900 focus:border-slate-900 focus:outline-none" />
        </div>
    </div>
    <input x-model="payDraft.reference" placeholder="Reference (optional)"
           class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12px] font-medium focus:border-slate-900 focus:outline-none" />
</div>
