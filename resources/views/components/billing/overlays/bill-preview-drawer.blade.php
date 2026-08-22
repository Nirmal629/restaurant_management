{{-- BillPreviewDrawer — thermal-style invoice preview, never navigates away. --}}
<x-pos.dialog name="preview" variant="drawer" width="max-w-lg" title="Invoice Preview" subtitle="Provisional until payment settles.">
    <div class="p-3">
        <div class="rounded-md border border-slate-300 bg-white">

            <div class="border-b border-dashed border-slate-300 px-4 py-3 text-center">
                <p class="text-[14px] font-black uppercase tracking-[0.05em] text-slate-900" x-text="venue.name"></p>
                <p class="text-[10.5px] leading-snug text-slate-500" x-text="venue.address"></p>
                <p class="pos-num text-[10.5px] text-slate-500">GSTIN <span x-text="venue.gstin"></span> · <span x-text="venue.phone"></span></p>

                <div class="pos-num mt-2 grid grid-cols-2 gap-x-3 gap-y-0.5 text-left text-[10.5px] text-slate-600">
                    <span>Invoice: <span class="font-bold text-slate-900" x-text="invoice.code"></span></span>
                    <span class="text-right">Order: <span class="font-bold text-slate-900" x-text="order.code"></span></span>
                    <span>Table: <span class="font-bold text-slate-900" x-text="order.type === 'dinein' ? order.table : order.type"></span></span>
                    <span class="text-right">Date: <span class="font-bold text-slate-900" x-text="invoice.createdLabel"></span></span>
                    <span>Waiter: <span class="font-bold text-slate-900" x-text="order.waiter || '—'"></span></span>
                    <span class="text-right">Cashier: <span class="font-bold text-slate-900" x-text="operator.name"></span></span>
                    <span class="col-span-2">Customer: <span class="font-bold text-slate-900" x-text="customer.name"></span></span>
                </div>
            </div>

            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-300 text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-500">
                        <th class="px-3 py-1.5 text-left">Item</th>
                        <th class="px-1 py-1.5 text-center">Qty</th>
                        <th class="px-1 py-1.5 text-right">Rate</th>
                        <th class="px-3 py-1.5 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="i in billableItems" :key="i.uid">
                        <tr class="border-b border-slate-100 align-top">
                            <td class="px-3 py-1.5">
                                <span class="text-[12px] font-semibold leading-tight text-slate-900" x-text="i.name"></span>
                                <span x-show="i.status !== 'normal'" class="block text-[10px] font-bold uppercase text-slate-400" x-text="itemStatusLabel(i.status)"></span>
                            </td>
                            <td class="pos-num px-1 py-1.5 text-center text-[12px] font-semibold text-slate-700" x-text="i.qty"></td>
                            <td class="pos-num px-1 py-1.5 text-right text-[12px] text-slate-600" x-text="money2(i.rate)"></td>
                            <td class="pos-num px-3 py-1.5 text-right text-[12px] font-bold text-slate-900" x-text="money2(netAmount(i))"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="border-t border-dashed border-slate-300 px-3 py-2">
                <dl class="space-y-0.5 text-[12px]">
                    <div class="flex justify-between"><dt class="text-slate-600">Subtotal</dt><dd class="pos-num font-semibold" x-text="money2(subtotal)"></dd></div>
                    <div class="flex justify-between" x-show="itemDiscountTotal"><dt class="text-slate-600">Item Discounts</dt><dd class="pos-num font-semibold text-amber-700" x-text="'− ' + money2(itemDiscountTotal)"></dd></div>
                    <div class="flex justify-between" x-show="complimentaryTotal"><dt class="text-slate-600">Complimentary</dt><dd class="pos-num font-semibold text-violet-700" x-text="'− ' + money2(complimentaryTotal)"></dd></div>
                    <div class="flex justify-between" x-show="billDiscountAmount"><dt class="text-slate-600">Bill Discount</dt><dd class="pos-num font-semibold text-amber-700" x-text="'− ' + money2(billDiscountAmount)"></dd></div>
                    <div class="flex justify-between border-t border-slate-200 pt-0.5"><dt class="text-slate-600">Taxable Amount</dt><dd class="pos-num font-semibold" x-text="money2(taxableAmount)"></dd></div>
                    <template x-if="charges.taxMode === 'exclusive'">
                        <div>
                            <div class="flex justify-between"><dt class="text-slate-600">CGST</dt><dd class="pos-num font-semibold" x-text="money2(cgstAmount)"></dd></div>
                            <div class="flex justify-between"><dt class="text-slate-600">SGST</dt><dd class="pos-num font-semibold" x-text="money2(sgstAmount)"></dd></div>
                        </div>
                    </template>
                    <div class="flex justify-between"><dt class="text-slate-600" x-text="charges.serviceLabel"></dt><dd class="pos-num font-semibold" x-text="money2(Math.round(taxableAmount * charges.serviceRate))"></dd></div>
                    <div class="flex justify-between" x-show="Math.abs(roundOff) >= 0.005"><dt class="text-slate-600">Round Off</dt><dd class="pos-num font-semibold" x-text="(roundOff >= 0 ? '+ ' : '− ') + money2(Math.abs(roundOff))"></dd></div>
                </dl>

                <div class="mt-1.5 flex items-baseline justify-between border-t-2 border-slate-900 pt-1.5">
                    <span class="text-[12.5px] font-black uppercase tracking-[0.06em] text-slate-900">Grand Total</span>
                    <span class="pos-num text-[20px] font-black tracking-tight text-slate-900" x-text="money2(grandTotal)"></span>
                </div>

                <div x-show="payments.length" class="mt-2 border-t border-dashed border-slate-300 pt-1.5">
                    <p class="mb-0.5 text-[9.5px] font-black uppercase tracking-wide text-slate-400">Payment Details</p>
                    <template x-for="p in payments" :key="p.at + p.method">
                        <div class="flex justify-between text-[11px]"><span class="text-slate-600" x-text="p.label"></span><span class="pos-num font-semibold text-slate-800" x-text="money2(p.amount)"></span></div>
                    </template>
                </div>

                <p class="mt-2 text-center text-[10px] text-slate-400">Thank you for dining with us — visit again!</p>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="grid grid-cols-2 gap-1.5">
            <button type="button" @click="printBill()" class="flex h-10 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
                <x-pos.icon name="printer" class="h-4 w-4" /> Thermal Receipt
            </button>
            <button type="button" @click="printBill()" class="flex h-10 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
                <x-pos.icon name="receipt" class="h-4 w-4" /> A4 Invoice
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
