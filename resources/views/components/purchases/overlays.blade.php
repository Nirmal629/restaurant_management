{{-- PO Detail --}}
<x-pos.dialog name="poDetail" variant="drawer" width="max-w-lg" title="Purchase Order" :subtitle="null">
    <template x-if="activeOrder">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2"><span class="pos-num text-[16px] font-black text-slate-900" x-text="activeOrder.id"></span><x-admin.badge expr="activeOrder.status" /></div>
                <p class="text-[12.5px] font-bold text-slate-800" x-text="activeOrder.supplier"></p>
            </div>
            <div class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Order Date</p><p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="activeOrder.date"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Expected Delivery</p><p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="activeOrder.expectedDelivery"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Created By</p><p class="text-[12.5px] font-bold text-slate-900" x-text="activeOrder.createdBy"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Approved By</p><p class="text-[12.5px] font-bold text-slate-900" x-text="activeOrder.approvedBy || '—'"></p></div>
            </div>
            <div class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Items</p>
                <div class="space-y-1"><template x-for="(l, i) in activeOrder.items" :key="i">
                    <div class="flex items-center justify-between rounded-md border border-slate-200 bg-white p-2">
                        <div><p class="text-[12px] font-semibold text-slate-800" x-text="l.ingredient"></p><p class="pos-num text-[10.5px] text-slate-400" x-text="l.qty + ' ' + l.unit + ' × ' + money(l.rate)"></p></div>
                        <span class="pos-num text-[12.5px] font-bold text-slate-900" x-text="money(lineAmount(l))"></span>
                    </div>
                </template></div>
                <div class="mt-2 space-y-1 rounded-md border border-slate-200 bg-slate-50 p-2.5 text-[12px]">
                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="pos-num font-semibold" x-text="money(poSubtotal(activeOrder))"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tax</span><span class="pos-num font-semibold" x-text="money(poTax(activeOrder))"></span></div>
                    <div class="flex justify-between" x-show="activeOrder.discount"><span class="text-slate-500">Discount</span><span class="pos-num font-semibold text-emerald-700" x-text="'− ' + money(activeOrder.discount)"></span></div>
                    <div class="flex justify-between" x-show="activeOrder.otherCharges"><span class="text-slate-500">Other Charges</span><span class="pos-num font-semibold" x-text="money(activeOrder.otherCharges)"></span></div>
                    <div class="flex justify-between border-t border-dashed border-slate-300 pt-1 font-bold text-slate-900"><span>Grand Total</span><span class="pos-num text-[15px]" x-text="money(poTotal(activeOrder))"></span></div>
                </div>
            </div>
        </div>
    </template>
    <x-slot:footer>
        <template x-if="activeOrder">
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" x-show="activeOrder.status === 'draft'" @click="requestApproval(activeOrder)" class="col-span-2 h-9 rounded-md bg-slate-900 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Submit for Approval</button>
                <button type="button" x-show="activeOrder.status === 'approval_pending'" @click="openApprove(activeOrder)" class="col-span-2 h-9 rounded-md bg-sky-600 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-sky-500">Approve</button>
                <button type="button" x-show="activeOrder.status === 'approved'" @click="markOrdered(activeOrder)" class="col-span-2 h-9 rounded-md bg-violet-600 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-violet-500">Mark Ordered</button>
                <button type="button" x-show="['ordered','partially_received'].includes(activeOrder.status)" @click="openReceiveGoods(activeOrder)" class="col-span-2 h-9 rounded-md bg-emerald-600 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-emerald-500">Receive Goods</button>
                <button type="button" x-show="!['received','cancelled'].includes(activeOrder.status)" @click="cancelPO(activeOrder)" class="col-span-2 h-9 rounded-md border border-rose-300 bg-white text-[11.5px] font-bold text-rose-600 hover:bg-rose-50">Cancel Order</button>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>


{{-- Create PO --}}
<x-pos.dialog name="poForm" width="max-w-2xl" title="New Purchase Order">
    <div class="space-y-3 p-4">
        <div class="grid grid-cols-2 gap-3">
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Supplier</label><select x-model="poDraft.supplier" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="s in suppliers" :key="s.id"><option :value="s.name" x-text="s.name"></option></template></select></div>
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Expected Delivery</label><input x-model="poDraft.expectedDelivery" type="date" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Reference</label><input x-model="poDraft.reference" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Notes</label><input x-model="poDraft.notes" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between"><p class="text-[10.5px] font-black uppercase tracking-wide text-slate-600">Items</p><button type="button" @click="addPoLine()" class="flex items-center gap-1 text-[11px] font-bold text-slate-600 hover:text-slate-900"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Add Line</button></div>
            <div class="space-y-1.5">
                <template x-for="(l, i) in poDraft.items" :key="i">
                    <div class="grid grid-cols-12 gap-1.5">
                        <input x-model="l.ingredient" placeholder="Ingredient" class="col-span-4 h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <input x-model="l.qty" type="number" placeholder="Qty" class="pos-num col-span-2 h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <input x-model="l.unit" placeholder="Unit" class="col-span-2 h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <input x-model="l.rate" type="number" placeholder="Rate" class="pos-num col-span-2 h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <input x-model="l.tax" type="number" placeholder="Tax %" class="pos-num col-span-1 h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <button type="button" @click="removePoLine(i)" class="col-span-1 grid h-8 place-items-center rounded border border-slate-200 text-slate-400 hover:border-rose-400 hover:text-rose-600"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Discount</label><input x-model="poDraft.discount" type="number" class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <div><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Other Charges</label><input x-model="poDraft.otherCharges" type="number" class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        </div>

        <div class="flex items-center justify-between rounded-md border border-slate-300 bg-slate-50 px-3 py-2">
            <span class="text-[11.5px] font-black uppercase tracking-wide text-slate-600">Grand Total</span>
            <span class="pos-num text-[18px] font-black text-slate-900" x-text="money(poDraftTotal)"></span>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="savePO()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Save Draft</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Approve --}}
<x-pos.dialog name="approve" width="max-w-sm" title="Approve Purchase Order" tone="dark">
    <template x-if="activeOrder">
        <div class="space-y-3 p-4">
            <p class="text-[13px] text-slate-700">Approve <span class="pos-num font-black text-slate-900" x-text="activeOrder.id"></span> for <span class="font-bold text-slate-900" x-text="money(poTotal(activeOrder))"></span>?</p>
            <div class="flex flex-wrap gap-1.5"><template x-for="r in approvalReasons" :key="r"><button type="button" @click="approvalDraft.reason = r" :class="approvalDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold" x-text="r"></button></template></div>
        </div>
    </template>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmApprove()" class="h-10 flex-1 rounded-md bg-sky-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-sky-500">Approve</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Receive goods --}}
<x-pos.dialog name="grnForm" width="max-w-2xl" title="Goods Receipt">
    <div class="space-y-3 p-4">
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">PO Reference</p><p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="grnDraft.poRef"></p></div>
            <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Supplier</p><p class="text-[12.5px] font-bold text-slate-900" x-text="grnDraft.supplier"></p></div>
            <div><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Invoice Number</label><input x-model="grnDraft.invoiceNumber" data-autofocus class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        </div>
        <div class="adm-table-wrap" style="max-height: 40vh;">
            <table class="adm-table">
                <thead><tr><th>Ingredient</th><th>Ordered</th><th>Prev. Received</th><th>Received Now</th><th>Rejected</th><th>Accepted</th></tr></thead>
                <tbody>
                    <template x-for="(l, i) in grnDraft.items" :key="i">
                        <tr>
                            <td class="font-semibold text-slate-800" x-text="l.ingredient"></td>
                            <td class="pos-num text-slate-500" x-text="l.ordered"></td>
                            <td class="pos-num text-slate-500" x-text="l.prevReceived"></td>
                            <td><input x-model.number="l.receivedNow" type="number" class="pos-num h-8 w-20 rounded border border-slate-300 px-2 text-[12px] font-bold focus:border-slate-900 focus:outline-none" /></td>
                            <td><input x-model.number="l.rejected" type="number" class="pos-num h-8 w-16 rounded border border-slate-300 px-2 text-[12px] font-bold focus:border-slate-900 focus:outline-none" /></td>
                            <td class="pos-num font-bold" :class="acceptedQty(l) < l.receivedNow ? 'text-amber-700' : 'text-emerald-700'" x-text="acceptedQty(l)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveGRN()" :disabled="!grnDraft.invoiceNumber?.trim()" class="h-10 flex-1 rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-300">Save Receipt</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- GRN detail --}}
<x-pos.dialog name="grnDetail" width="max-w-lg" title="Goods Receipt Detail" :subtitle="null">
    <template x-if="activeGrn">
        <div class="p-4">
            <div class="mb-3 grid grid-cols-2 gap-2">
                <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">GRN Number</p><p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="activeGrn.id"></p></div>
                <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Invoice #</p><p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="activeGrn.invoiceNumber"></p></div>
            </div>
            <div class="space-y-1.5">
                <template x-for="(l, i) in activeGrn.items" :key="i">
                    <div class="rounded-md border border-slate-200 bg-white p-2.5">
                        <div class="flex justify-between"><span class="font-semibold text-slate-800" x-text="l.ingredient"></span><span class="pos-num font-bold" :class="l.rejected ? 'text-amber-700' : 'text-emerald-700'" x-text="acceptedQty(l) + ' accepted'"></span></div>
                        <p class="text-[10.5px] text-slate-400" x-text="'Ordered ' + l.ordered + ' · Received ' + l.receivedNow + (l.rejected ? ' · Rejected ' + l.rejected : '')"></p>
                    </div>
                </template>
            </div>
        </div>
    </template>
</x-pos.dialog>


{{-- Supplier detail --}}
<x-pos.dialog name="supplierDetail" variant="drawer" width="max-w-md" title="Supplier" :subtitle="null">
    <template x-if="activeSupplier">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <p class="text-[14px] font-black text-slate-900" x-text="activeSupplier.name"></p>
                <p class="text-[11.5px] font-semibold text-slate-500" x-text="activeSupplier.contact + ' · ' + activeSupplier.phone"></p>
            </div>
            <div class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                <div class="col-span-2 rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Address</p><p class="text-[12px] text-slate-800" x-text="activeSupplier.address"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">GSTIN</p><p class="pos-num text-[12px] font-bold text-slate-900" x-text="activeSupplier.gstin || '—'"></p></div>
                <div class="rounded-md border border-amber-200 bg-amber-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-amber-700">Outstanding</p><p class="pos-num text-[13px] font-black text-amber-800" x-text="money(activeSupplier.outstanding)"></p></div>
            </div>
            <div class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Items Supplied</p>
                <div class="mb-3 flex flex-wrap gap-1.5"><template x-for="i in activeSupplier.items" :key="i"><span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700" x-text="i"></span></template></div>
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Purchase History</p>
                <div class="space-y-1.5">
                    <template x-for="o in supplierHistory(activeSupplier.name)" :key="o.id">
                        <div class="flex items-center justify-between rounded-md border border-slate-200 bg-white p-2.5">
                            <div><p class="pos-num text-[11.5px] font-bold text-slate-900" x-text="o.id"></p><p class="text-[10px] text-slate-400" x-text="o.date"></p></div>
                            <span class="pos-num text-[12px] font-bold text-slate-900" x-text="money(poTotal(o))"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</x-pos.dialog>
