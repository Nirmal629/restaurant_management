{{-- CompletePaymentButton — disabled until Due = ₹0; no extra confirmation step when it's valid. --}}
<div class="pos-dock border-t border-slate-200 bg-white p-2.5">
    <button type="button" @click="completePayment()" :disabled="saving || dueAmount > 0" :aria-busy="saving ? 'true' : 'false'"
            :class="saving || dueAmount > 0 ? 'cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-emerald-600 text-white hover:bg-emerald-500'"
            class="flex h-12 w-full items-center justify-center gap-2 rounded-md text-[14px] font-black uppercase tracking-[0.06em]">
        <x-pos.icon name="check" class="h-5 w-5" stroke="2.6" />
        <span x-show="dueAmount > 0" x-text="'Due ' + money(dueAmount)"></span>
        <span x-show="dueAmount <= 0">Complete Payment</span>
        <kbd class="hidden rounded bg-black/10 px-1 text-[9.5px] font-bold xl:inline">F9</kbd>
    </button>
</div>
