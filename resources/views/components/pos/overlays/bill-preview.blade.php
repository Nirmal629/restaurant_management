{{-- BillPreviewDrawer — never navigates away; payment opens on top of it --}}
<x-pos.dialog name="bill" variant="drawer" width="max-w-lg" title="Bill preview"
              subtitle="Not yet printed · cancelled lines excluded">

    <x-slot:headerActions>
        <span class="pos-num rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-bold text-slate-700"
              x-text="'#' + order.code"></span>
    </x-slot:headerActions>

    <div class="p-3">
        <div class="rounded-md border border-slate-300 bg-white">

            {{-- Bill head --}}
            <div class="border-b border-dashed border-slate-300 px-4 py-3 text-center">
                <p class="text-[14px] font-black uppercase tracking-[0.05em] text-slate-900" x-text="venue.name"></p>
                <p class="text-[10.5px] leading-snug text-slate-500" x-text="venue.address"></p>
                <p class="pos-num text-[10.5px] text-slate-500">
                    GSTIN <span x-text="venue.gstin"></span> · <span x-text="venue.phone"></span>
                </p>
                <div class="pos-num mt-2 grid grid-cols-2 gap-x-3 gap-y-0.5 text-left text-[10.5px] text-slate-600">
                    <span>Bill: <span class="font-bold text-slate-900" x-text="order.code"></span></span>
                    <span class="text-right">Date: <span class="font-bold text-slate-900" x-text="new Date().toLocaleDateString('en-IN')"></span></span>
                    <template x-if="orderType === 'dinein'">
                        <span>Table: <span class="font-bold text-slate-900" x-text="order.table + ' · ' + order.guests + ' pax'"></span></span>
                    </template>
                    <template x-if="orderType !== 'dinein'">
                        <span>Type: <span class="font-bold uppercase text-slate-900" x-text="orderType"></span></span>
                    </template>
                    <span class="text-right">Time: <span class="font-bold text-slate-900" x-text="clock"></span></span>
                    <span>Steward: <span class="font-bold text-slate-900" x-text="order.waiter"></span></span>
                    <span class="text-right" x-show="order.customer">Guest: <span class="font-bold text-slate-900" x-text="order.customer?.name"></span></span>
                </div>
            </div>

            {{-- Lines --}}
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
                    <template x-for="l in billableLines" :key="l.uid">
                        <tr class="border-b border-slate-100 align-top">
                            <td class="px-3 py-1.5">
                                <span class="text-[12px] font-semibold leading-tight text-slate-900" x-text="l.name"></span>
                                <span x-show="l.variant || l.modifiers.length" class="block text-[10px] text-slate-500"
                                      x-text="[l.variant, ...l.modifiers.map(m => m.label)].filter(Boolean).join(' · ')"></span>
                            </td>
                            <td class="pos-num px-1 py-1.5 text-center text-[12px] font-semibold text-slate-700" x-text="l.qty"></td>
                            <td class="pos-num px-1 py-1.5 text-right text-[12px] text-slate-600" x-text="money2(unitPrice(l))"></td>
                            <td class="pos-num px-3 py-1.5 text-right text-[12px] font-bold text-slate-900" x-text="money2(lineTotal(l))"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            {{-- Summary --}}
            <div class="border-t border-dashed border-slate-300 px-3 py-2">
                <dl class="space-y-0.5 text-[12px]">
                    <div class="flex justify-between"><dt class="text-slate-600">Subtotal</dt><dd class="pos-num font-semibold" x-text="money2(subtotal)"></dd></div>
                    <div class="flex justify-between" x-show="discountAmount">
                        <dt class="text-slate-600">Discount <span class="text-[10px] text-slate-400" x-text="discount?.reason"></span></dt>
                        <dd class="pos-num font-semibold text-emerald-700" x-text="'− ' + money2(discountAmount)"></dd>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-0.5"><dt class="text-slate-600">Taxable value</dt><dd class="pos-num font-semibold" x-text="money2(taxableBase)"></dd></div>
                    <template x-for="t in charges.taxSplit" :key="t.label">
                        <div class="flex justify-between">
                            <dt class="text-slate-600"><span x-text="t.label"></span> <span class="pos-num text-[10px] text-slate-400" x-text="'@ ' + (t.rate * 100) + '%'"></span></dt>
                            <dd class="pos-num font-semibold" x-text="money2(taxableBase * t.rate)"></dd>
                        </div>
                    </template>
                    <div class="flex justify-between" x-show="charges.serviceEnabled">
                        <dt class="text-slate-600"><span x-text="charges.serviceLabel"></span> <span class="pos-num text-[10px] text-slate-400" x-text="'@ ' + (charges.serviceRate * 100) + '%'"></span></dt>
                        <dd class="pos-num font-semibold" x-text="money2(serviceAmount)"></dd>
                    </div>
                    <div class="flex justify-between" x-show="Math.abs(roundOff) >= 0.005">
                        <dt class="text-slate-600">Round off</dt>
                        <dd class="pos-num font-semibold" x-text="(roundOff >= 0 ? '+ ' : '− ') + money2(Math.abs(roundOff))"></dd>
                    </div>
                </dl>

                <div class="mt-1.5 flex items-baseline justify-between border-t-2 border-slate-900 pt-1.5">
                    <span class="text-[12.5px] font-black uppercase tracking-[0.06em] text-slate-900">Grand Total</span>
                    <span class="pos-num text-[20px] font-black tracking-tight text-slate-900" x-text="money2(total)"></span>
                </div>

                <p class="mt-2 text-center text-[10px] text-slate-400">
                    Provisional bill — printed copy is issued after settlement.
                </p>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="grid grid-cols-4 gap-1.5">
            <button type="button" @click="back()"
                    class="h-11 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Back</button>
            <button type="button" @click="openSplit()"
                    class="h-11 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Split bill</button>
            <button type="button" @click="notify('Bill sent to counter printer')"
                    class="flex h-11 items-center justify-center gap-1.5 rounded-md bg-slate-800 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-900">
                <x-pos.icon name="printer" class="h-4 w-4" /> Print
            </button>
            <button type="button" @click="openPayment()"
                    class="h-11 rounded-md bg-emerald-600 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">Payment</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
