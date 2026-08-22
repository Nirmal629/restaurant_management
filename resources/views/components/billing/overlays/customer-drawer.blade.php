{{-- CustomerBillingDrawer — phone-first lookup, quick add, GST toggle only surfaced when asked for. --}}
<x-pos.dialog name="customer" variant="drawer" width="max-w-md" title="Customer" subtitle="Search by phone or name.">
    <div class="border-b border-slate-200 bg-white p-3">
        <div class="relative">
            <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input x-model="customerQuery" data-autofocus inputmode="tel" placeholder="Phone number or name…"
                   class="h-10 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-[13px] font-medium focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900" />
        </div>

        <div class="mt-2 flex items-center gap-2 rounded-md border border-slate-300 bg-slate-50 px-2.5 py-2">
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12.5px] font-bold text-slate-900" x-text="customer.name"></p>
                <p class="pos-num text-[11px] text-slate-500" x-text="customer.phone"></p>
            </div>
            <span class="text-[9.5px] font-bold uppercase tracking-wide text-emerald-700">Attached</span>
        </div>
    </div>

    <div class="p-3">
        <p class="mb-2 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400" x-text="customerQuery ? 'Matches' : 'Recent guests'"></p>

        <div class="space-y-1.5">
            <template x-for="c in visibleCustomers" :key="c.id">
                <div class="flex items-center gap-2.5 rounded-md border border-slate-200 bg-white p-2.5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-[13px] font-bold text-slate-700" x-text="c.name.charAt(0)"></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-bold text-slate-900" x-text="c.name"></p>
                        <p class="pos-num text-[11px] font-medium text-slate-500" x-text="c.phone"></p>
                        <p class="pos-num mt-0.5 flex gap-2 text-[10px] font-semibold text-slate-400">
                            <span x-text="c.visits + ' visits'"></span><span x-text="c.points + ' pts'"></span>
                        </p>
                    </div>
                    <button type="button" @click="pickCustomer(c)" class="h-8 shrink-0 rounded-md bg-slate-900 px-3 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Select</button>
                </div>
            </template>
        </div>

        <div class="mt-3 rounded-md border border-slate-300 bg-slate-50 p-3">
            <p class="mb-2 text-[12px] font-black uppercase tracking-[0.07em] text-slate-700">Quick add guest</p>
            <div class="space-y-2">
                <input x-model="customerDraft.name" placeholder="Guest name" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                <input x-model="customerDraft.phone" inputmode="tel" maxlength="10" placeholder="Mobile number" class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                <button type="button" @click="quickAddCustomer()" :disabled="!customerDraft.name.trim() || customerDraft.phone.trim().length < 10"
                        class="h-9 w-full rounded-md bg-slate-900 text-[12px] font-bold uppercase tracking-wide text-white disabled:cursor-not-allowed disabled:bg-slate-300">Save &amp; Attach</button>
            </div>
        </div>

        <button type="button" @click="swap('gst')" class="mt-2 flex h-9 w-full items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
            <x-pos.icon name="receipt" class="h-3.5 w-3.5" /> Business / GST Invoice Details
        </button>
    </div>
</x-pos.dialog>
