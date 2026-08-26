{{-- BillingHeader — ~52px. Identity · invoice/order codes · cashier telemetry + quick actions. --}}
<header class="pos-header z-30 flex items-center gap-3 border-b border-slate-800 bg-slate-900 px-3 text-slate-100">

    <a href="{{ route('app.start') }}" class="flex shrink-0 items-center gap-2 rounded px-1 py-1 hover:bg-slate-800">
        <span class="grid h-7 w-7 place-items-center rounded bg-brand-600 text-[11px] font-black text-white">RB</span>
        <span class="hidden leading-none sm:block">
            <span class="block text-[13px] font-bold text-white" x-text="venue.name"></span>
            <span class="mt-0.5 block text-[10px] font-medium text-slate-400" x-text="venue.branch"></span>
        </span>
    </a>

    <span class="rounded-md bg-slate-800 px-2 py-1.5 text-[11px] font-black uppercase tracking-[0.08em] text-amber-400 shrink-0">Billing</span>

    <div class="mx-auto flex shrink-0 items-center gap-4 text-center">
        <div>
            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-500">Invoice</p>
            <p class="pos-num text-[13px] font-black text-white" x-text="invoice.code"></p>
        </div>
        <div class="h-6 w-px bg-slate-700"></div>
        <div>
            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-500">Order</p>
            <p class="pos-num text-[13px] font-black text-white" x-text="order.code"></p>
        </div>
        <span :class="statusClass(billStatus)" class="pos-num rounded border px-2 py-1 text-[9.5px] font-black uppercase tracking-wide" x-text="statusLabel(billStatus)"></span>
    </div>

    <div class="flex shrink-0 items-center gap-1.5">
        <x-billing.printer-status />

        <button type="button" @click="goToPos()"
                class="hidden h-8 items-center gap-1.5 rounded-md border border-slate-700 px-2.5 text-[10.5px] font-bold uppercase tracking-wide text-slate-200 hover:border-slate-500 lg:flex">
            <x-pos.icon name="chevron-left" class="h-3.5 w-3.5" /> Back to POS
        </button>

        <button type="button" @click="printBill()"
                class="flex h-8 items-center gap-1.5 rounded-md border border-slate-700 px-2.5 text-[10.5px] font-bold uppercase tracking-wide text-slate-200 hover:border-slate-500">
            <x-pos.icon name="printer" class="h-3.5 w-3.5" /> Print
        </button>

        <div class="relative" x-data @click.outside="moreOpen = false">
            <button type="button" @click="moreOpen = !moreOpen"
                    :class="moreOpen ? 'border-slate-500 bg-slate-800' : 'border-slate-700'"
                    class="flex h-8 items-center gap-1 rounded-md border px-2 text-slate-200 hover:border-slate-500">
                <span class="text-[10.5px] font-bold uppercase tracking-wide">More</span>
                <x-pos.icon name="chevron-down" class="h-3.5 w-3.5" />
            </button>
            <div x-show="moreOpen" x-cloak x-transition.origin.top.right
                 class="absolute right-0 top-9 z-30 w-60 overflow-hidden rounded-md border border-slate-700 bg-slate-900 py-1 shadow-2xl">
                <template x-for="a in moreActions" :key="a.key">
                    <button type="button" @click="runMore(a.key)"
                            :class="a.danger ? 'text-rose-400 hover:bg-rose-950' : 'text-slate-200 hover:bg-slate-800'"
                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12px] font-semibold" x-text="a.label"></button>
                </template>
            </div>
        </div>

        <div class="h-6 w-px bg-slate-700"></div>

        <div class="text-right leading-none">
            <p class="pos-num text-[13px] font-bold text-white" x-text="clock"></p>
            <p class="mt-0.5 text-[9.5px] font-bold uppercase tracking-[0.08em] text-emerald-400">Shift <span x-text="operator.shift"></span></p>
        </div>
        <span class="hidden items-center gap-1.5 rounded-md px-1.5 py-1 lg:flex">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-700 text-[11px] font-bold text-white" x-text="operator.initials"></span>
            <span class="text-left leading-none">
                <span class="block text-[11.5px] font-bold text-white" x-text="operator.role + ' ' + operator.name"></span>
                <span class="mt-0.5 block text-[9.5px] font-medium uppercase tracking-wide text-slate-400" x-text="operator.terminal"></span>
            </span>
        </span>
    </div>
</header>
