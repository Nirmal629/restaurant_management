{{-- CashPaymentPanel --}}
<div x-show="payDraft.method === 'cash'" class="space-y-2">
    <div>
        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.08em] text-slate-500">Amount Received</label>
        <div class="relative">
            <span class="pos-num absolute left-3 top-1/2 -translate-y-1/2 text-[17px] font-bold text-slate-400">₹</span>
            <input x-ref="payAmountCash" x-model="payDraft.amount" type="number" min="0" inputmode="decimal"
                   class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-right text-[22px] font-black tracking-tight text-slate-900 focus:border-slate-900 focus:outline-none" />
        </div>
    </div>
    <div class="grid grid-cols-5 gap-1.5">
        <button type="button" @click="quickCash(dueAmount)" class="h-9 rounded-md border border-slate-900 bg-slate-900 text-[10.5px] font-bold uppercase tracking-wide text-white">Exact</button>
        @foreach ([500, 1000, 2000, 5000] as $note)
            <button type="button" @click="quickCash({{ $note }})" class="pos-num h-9 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">{{ $note }}</button>
        @endforeach
    </div>
    <div x-show="cashChange > 0" class="flex items-center justify-between rounded-md border-2 border-amber-400 bg-amber-50 px-3 py-2">
        <span class="text-[11px] font-black uppercase tracking-[0.06em] text-amber-900">Change Due</span>
        <span class="pos-num text-[19px] font-black text-amber-900" x-text="money(cashChange)"></span>
    </div>
</div>
