{{-- GstInvoiceForm — only surfaced on demand, never shown for normal walk-in customers. --}}
<x-pos.dialog name="gst" width="max-w-md" title="Business / GST Invoice">
    <div class="space-y-3 p-4">
        <label class="flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2.5">
            <input type="checkbox" x-model="gstInvoice" class="h-4 w-4 accent-slate-900">
            <span class="text-[12.5px] font-bold text-slate-700">This is a business / GST invoice</span>
        </label>

        <div class="space-y-2" :class="!gstInvoice && 'pointer-events-none opacity-40'">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Business Name</label>
                <input x-model="customer.businessName" data-autofocus placeholder="Registered business name"
                       class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">GSTIN</label>
                <input x-model="customer.gstin" placeholder="22AAAAA0000A1Z5" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium uppercase focus:border-slate-900 focus:outline-none" />
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Billing Address</label>
                <textarea x-model="customer.address" rows="2" placeholder="Street, city, PIN"
                          class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <button type="button" @click="back()" class="h-10 w-full rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Save</button>
    </x-slot:footer>
</x-pos.dialog>
