{{--
    PaymentPanel — recap (dock) + scrollable method area + MixedPaymentRows +
    CompletePaymentButton (dock). Grand Total / Paid / Due stay visible here
    regardless of what the cashier scrolls to in the other two columns.

    $attributes is merged onto the root so the caller can bind a reactive
    class directly onto this flex item (see BillSummary for why a wrapping
    <div> instead breaks the flex sizing).
--}}
<section {{ $attributes->merge(['class' => 'bil-payment-col bg-white']) }}>

    {{-- PaymentTotals recap --}}
    <div class="pos-dock grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200 bg-slate-50">
        <div class="px-3 py-2">
            <p class="text-[9px] font-black uppercase tracking-[0.09em] text-slate-400">Grand Total</p>
            <p class="pos-num text-[17px] font-black leading-tight text-slate-900" x-text="money(grandTotal)"></p>
        </div>
        <div class="px-3 py-2">
            <p class="text-[9px] font-black uppercase tracking-[0.09em] text-slate-400">Paid</p>
            <p class="pos-num text-[17px] font-black leading-tight text-emerald-700" x-text="money(paidTotal)"></p>
        </div>
        <div class="px-3 py-2" :class="dueAmount > 0 ? 'bg-rose-50' : 'bg-emerald-50'">
            <p class="text-[9px] font-black uppercase tracking-[0.09em] text-slate-400">Due</p>
            <p class="pos-num text-[17px] font-black leading-tight" :class="dueAmount > 0 ? 'text-rose-600' : 'text-emerald-700'" x-text="money(dueAmount)"></p>
        </div>
    </div>

    {{-- Split-active lock state — settlement happens in the Split Bill drawer instead --}}
    <template x-if="split.active">
        <div class="pos-scroll flex flex-col items-center justify-center gap-2 p-6 text-center">
            <x-pos.icon name="split" class="h-7 w-7 text-slate-300" />
            <p class="text-[13px] font-bold text-slate-700">This bill has been split into <span x-text="split.bills.length"></span> parts</p>
            <p class="max-w-[220px] text-[11.5px] text-slate-500">Settle each part from the Split Bill drawer — the single-invoice payment panel is disabled while a split is active.</p>
            <button type="button" @click="openSplit()" class="mt-1 h-9 rounded-md bg-slate-900 px-4 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Open Split Bill</button>
            <button type="button" @click="clearSplit()" class="text-[10.5px] font-bold text-slate-400 underline decoration-slate-300 underline-offset-2 hover:text-slate-700">Cancel split — bill as one invoice</button>
        </div>
    </template>

    <template x-if="!split.active">
        <div class="contents">
            <div class="pos-scroll space-y-3 p-3">

                {{-- Quick payment — full settlement in one tap --}}
                <div>
                    <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-500">Quick Payment</p>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" @click="quickFull('cash')" :disabled="dueAmount <= 0" class="h-10 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900 disabled:opacity-40">Cash · Exact</button>
                        <button type="button" @click="quickFull('upi')" :disabled="dueAmount <= 0" class="h-10 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900 disabled:opacity-40">UPI · Full</button>
                        <button type="button" @click="quickFull('credit')" :disabled="dueAmount <= 0" class="h-10 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900 disabled:opacity-40">Card · Full</button>
                    </div>
                </div>

                <div>
                    <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-500">Payment Method</p>
                    <x-billing.payment-method-selector />
                </div>

                <x-billing.cash-payment-panel />
                <x-billing.upi-payment-panel />
                <x-billing.card-payment-panel />
                <x-billing.generic-payment-panel />

                <button type="button" @click="addPayment()" :disabled="!(Number(payDraft.amount) > 0) || dueAmount <= 0"
                        class="flex h-10 w-full items-center justify-center gap-1.5 rounded-md bg-slate-900 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                    <x-pos.icon name="plus" class="h-4 w-4" stroke="2.4" /> Add Payment Method
                </button>

                <x-billing.mixed-payment-rows />
            </div>

            <x-billing.complete-payment-button />
        </div>
    </template>
</section>
