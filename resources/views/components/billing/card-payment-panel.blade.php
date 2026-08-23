{{-- CardPaymentPanel — reference metadata only, never full card numbers. --}}
<div x-show="['credit', 'debit'].includes(payDraft.method)" class="space-y-2">
    <div>
        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.08em] text-slate-500">Amount</label>
        <div class="relative">
            <span class="pos-num absolute left-3 top-1/2 -translate-y-1/2 text-[17px] font-bold text-slate-400">₹</span>
            <input x-ref="payAmountCard" x-model="payDraft.amount" type="number" min="0" inputmode="decimal"
                   class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-right text-[22px] font-black tracking-tight text-slate-900 focus:border-slate-900 focus:outline-none" />
        </div>
    </div>

    <div class="grid grid-cols-2 gap-1.5">
        <button type="button" @click="payDraft.method = 'credit'" :class="payDraft.method === 'credit' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700'" class="h-9 rounded-md border text-[11.5px] font-bold">Credit</button>
        <button type="button" @click="payDraft.method = 'debit'" :class="payDraft.method === 'debit' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700'" class="h-9 rounded-md border text-[11.5px] font-bold">Debit</button>
    </div>

    <div class="grid grid-cols-2 gap-1.5">
        <input x-model="payDraft.last4" maxlength="4" inputmode="numeric" placeholder="Last 4 digits"
               class="pos-num h-9 rounded-md border border-slate-300 bg-white px-2.5 text-[12px] font-medium focus:border-slate-900 focus:outline-none" />
        <input x-model="payDraft.reference" placeholder="Approval code"
               class="h-9 rounded-md border border-slate-300 bg-white px-2.5 text-[12px] font-medium focus:border-slate-900 focus:outline-none" />
    </div>
    <p class="text-[10px] text-slate-400">Reference metadata only — full card numbers are never captured here.</p>
</div>
