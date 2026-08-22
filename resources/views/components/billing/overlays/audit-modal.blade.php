{{-- Audit Information — read-only trail of who touched this invoice and when. --}}
<x-pos.dialog name="audit" width="max-w-md" title="Audit Information" subtitle="Read-only trail for this invoice.">
    <div class="space-y-1.5 p-3">
        <div class="rounded-md border border-slate-200 bg-white p-2.5">
            <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Invoice generated</p>
            <p class="text-[12px] font-semibold text-slate-800" x-text="operator.role + ' ' + operator.name + ' · ' + invoice.createdLabel"></p>
        </div>
        <div x-show="billDiscount?.approvedBy" class="rounded-md border border-slate-200 bg-white p-2.5">
            <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Bill discount approved</p>
            <p class="text-[12px] font-semibold text-slate-800" x-text="billDiscount?.approvedBy + ' · ' + billDiscount?.reason"></p>
        </div>
        <template x-for="i in items.filter(x => x.status === 'complimentary')" :key="i.uid">
            <div class="rounded-md border border-slate-200 bg-white p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Complimentary approved</p>
                <p class="text-[12px] font-semibold text-slate-800" x-text="i.compBy + ' · ' + i.name + ' · ' + i.compReason"></p>
            </div>
        </template>
        <template x-for="(r, idx) in refunds" :key="idx">
            <div class="rounded-md border border-slate-200 bg-white p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Refund approved</p>
                <p class="text-[12px] font-semibold text-slate-800" x-text="r.approvedBy + ' · ' + money(r.amount) + ' · ' + r.reason"></p>
            </div>
        </template>
        <div x-show="invoice.voided" class="rounded-md border border-rose-200 bg-rose-50 p-2.5">
            <p class="text-[9.5px] font-black uppercase tracking-wide text-rose-700">Invoice voided</p>
            <p class="text-[12px] font-semibold text-rose-800" x-text="invoice.voidReason"></p>
        </div>
    </div>
</x-pos.dialog>
