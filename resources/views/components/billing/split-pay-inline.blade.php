{{--
    Inline mini payment capture for one split bill — settles independently
    without leaving the drawer. Targets whichever bill store.splitPay.idx
    currently points at, set by the caller's openSplitPay(bill) click.
--}}
<div class="mt-2 space-y-1.5 rounded-md border border-slate-300 bg-slate-50 p-2" @click.stop>
    <div class="grid grid-cols-3 gap-1">
        @foreach (['cash' => 'Cash', 'upi' => 'UPI', 'credit' => 'Card'] as $k => $l)
            <button type="button" @click="splitPay.method = '{{ $k }}'"
                    :class="splitPay.method === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                    class="h-7 rounded border text-[9.5px] font-bold uppercase">{{ $l }}</button>
        @endforeach
    </div>
    <input x-model="splitPay.amount" type="number" min="0" data-autofocus
           class="pos-num h-8 w-full rounded border border-slate-300 bg-white px-2 text-right text-[13px] font-bold focus:border-slate-900 focus:outline-none" />
    <div class="grid grid-cols-2 gap-1">
        <button type="button" @click="cancelSplitPay()" class="h-7 rounded border border-slate-300 bg-white text-[10px] font-bold uppercase text-slate-600">Cancel</button>
        <button type="button" @click="confirmSplitPay()" class="h-7 rounded bg-emerald-600 text-[10px] font-black uppercase text-white hover:bg-emerald-500">Confirm</button>
    </div>
</div>
