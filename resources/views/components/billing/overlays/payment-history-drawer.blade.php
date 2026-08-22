{{-- PaymentHistoryDrawer — every tender + any refunds/voids logged against this invoice. --}}
<x-pos.dialog name="history" variant="drawer" width="max-w-md" title="Payment History" :subtitle="null">
    <div class="space-y-1.5 p-3">
        <template x-for="(p, idx) in payments" :key="'p' + idx">
            <div class="flex items-center gap-2.5 rounded-md border border-slate-200 bg-white p-2.5">
                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10.5px] font-black uppercase tracking-wide text-slate-700" x-text="p.label"></span>
                <div class="min-w-0 flex-1">
                    <p class="pos-num text-[10.5px] font-semibold text-slate-500" x-text="p.at + (p.reference ? ' · ' + p.reference : '')"></p>
                </div>
                <span class="pos-num text-[13px] font-bold text-slate-900" x-text="money(p.amount)"></span>
                <span class="rounded border border-emerald-300 bg-emerald-50 px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-emerald-700">Success</span>
            </div>
        </template>

        <template x-for="(r, idx) in refunds" :key="'r' + idx">
            <div class="flex items-center gap-2.5 rounded-md border border-rose-200 bg-rose-50 p-2.5">
                <span class="rounded bg-rose-100 px-1.5 py-0.5 text-[10.5px] font-black uppercase tracking-wide text-rose-700" x-text="r.method"></span>
                <div class="min-w-0 flex-1">
                    <p class="pos-num text-[10.5px] font-semibold text-rose-600" x-text="r.at + ' · ' + r.reason"></p>
                </div>
                <span class="pos-num text-[13px] font-bold text-rose-700" x-text="'− ' + money(r.amount)"></span>
                <span class="rounded border border-rose-400 bg-rose-100 px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-rose-700">Refunded</span>
            </div>
        </template>

        <div x-show="invoice.voided" class="flex items-center gap-2.5 rounded-md border border-rose-300 bg-rose-100 p-2.5">
            <x-pos.icon name="ban" class="h-4 w-4 shrink-0 text-rose-700" />
            <p class="flex-1 text-[11.5px] font-bold text-rose-800" x-text="'Invoice voided — ' + invoice.voidReason"></p>
            <span class="rounded border border-rose-400 bg-white px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-rose-700">Voided</span>
        </div>

        <p x-show="!payments.length && !refunds.length" class="py-14 text-center text-[12.5px] font-semibold text-slate-400">No payment activity yet.</p>
    </div>
</x-pos.dialog>
