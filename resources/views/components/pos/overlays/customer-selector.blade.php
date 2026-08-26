{{-- CustomerSelectorDrawer — phone-first lookup, two-field quick add, no CRM form --}}
<x-pos.dialog name="customer" variant="drawer" width="max-w-md" title="Customer"
              subtitle="Search by phone or name. Quick add takes two fields.">

    <div class="border-b border-slate-200 bg-white p-3">
        <div class="relative">
            <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input x-model="customerQuery" data-autofocus inputmode="tel" name="pos_customer_lookup"
                   autocomplete="off" autocorrect="off" autocapitalize="words" spellcheck="false" placeholder="Phone number or name…"
                   class="h-10 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-[13px] font-medium focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900" />
        </div>

        <div x-show="order.customer" class="mt-2 flex items-center gap-2 rounded-md border border-slate-300 bg-slate-50 px-2.5 py-2">
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12.5px] font-bold text-slate-900" x-text="order.customer?.name"></p>
                <p class="pos-num text-[11px] text-slate-500" x-text="order.customer?.phone"></p>
            </div>
            <span class="text-[9.5px] font-bold uppercase tracking-wide text-emerald-700">Attached</span>
            <button type="button" @click="detachCustomer()"
                    class="rounded border border-slate-300 px-2 py-1 text-[11px] font-bold text-slate-600 hover:border-rose-500 hover:text-rose-600">Remove</button>
        </div>
    </div>

    {{-- Results --}}
    <div class="p-3">
        <p class="mb-2 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400"
           x-text="customerQuery ? 'Matches' : 'Recent guests'"></p>

        <div class="space-y-1.5">
            <template x-for="c in visibleCustomers" :key="c.id">
                <div class="flex items-center gap-2.5 rounded-md border border-slate-200 bg-white p-2.5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-[13px] font-bold text-slate-700"
                          x-text="c.name.charAt(0)"></span>
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-1.5 truncate text-[13px] font-bold text-slate-900">
                            <span x-text="c.name"></span>
                            <span x-show="c.tag" class="rounded bg-amber-100 px-1 text-[9px] font-bold uppercase tracking-wide text-amber-800" x-text="c.tag"></span>
                        </p>
                        <p class="pos-num text-[11px] font-medium text-slate-500" x-text="c.phone"></p>
                        <p class="pos-num mt-0.5 flex gap-2 text-[10px] font-semibold text-slate-400">
                            <span x-text="c.visits + ' visits'"></span>
                            <span x-text="money(c.spend) + ' spend'"></span>
                            <span x-text="c.points + ' pts'"></span>
                        </p>
                    </div>
                    <button type="button" @click="pickCustomer(c)"
                            class="h-8 shrink-0 rounded-md bg-slate-900 px-3 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Select</button>
                </div>
            </template>

            <p x-show="!visibleCustomers.length" class="rounded-md border border-dashed border-slate-300 bg-slate-50 py-8 text-center text-[12.5px] font-semibold text-slate-500">
                No guest found for “<span x-text="customerQuery"></span>”
            </p>
        </div>

        {{-- Quick add --}}
        <div class="mt-3 rounded-md border border-slate-300 bg-slate-50 p-3">
            <button type="button" @click="customerCreating = !customerCreating; customerDraft.phone = customerDraft.phone || (/^\d+$/.test(customerQuery) ? customerQuery : ''); customerDraft.name = customerDraft.name || (/^\d+$/.test(customerQuery) ? '' : customerQuery)"
                    class="flex w-full items-center gap-2 text-[12px] font-black uppercase tracking-[0.07em] text-slate-700">
                <x-pos.icon name="plus" class="h-4 w-4" stroke="2.4" />
                Quick add guest
                <span class="flex-1"></span>
                <x-pos.icon name="chevron-down" class="h-4 w-4 text-slate-400 transition-transform"
                            x-bind:class="customerCreating && 'rotate-180'" />
            </button>

            <div x-show="customerCreating" x-cloak class="mt-2.5 space-y-2">
                <input x-model="customerDraft.name" name="guest_name" autocomplete="name" placeholder="Guest name"
                       class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                <input x-model="customerDraft.phone" inputmode="tel" name="guest_phone" autocomplete="tel" maxlength="10" placeholder="Mobile number"
                       class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                <button type="button" @click="quickAddCustomer()"
                        :disabled="!customerDraft.name.trim() || customerDraft.phone.trim().length < 10"
                        class="h-9 w-full rounded-md bg-slate-900 text-[12px] font-bold uppercase tracking-wide text-white disabled:cursor-not-allowed disabled:bg-slate-300">
                    Save &amp; attach
                </button>
                <p class="text-[10.5px] text-slate-500">Full profile, addresses and preferences are edited in Customers — not at the terminal.</p>
            </div>
        </div>
    </div>
</x-pos.dialog>
