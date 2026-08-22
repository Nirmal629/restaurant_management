{{-- RunningOrdersDrawer — switch between live orders without leaving the POS --}}
<x-pos.dialog name="running" variant="drawer" width="max-w-md" title="Running orders"
              subtitle="Tap an order to load it into this terminal.">

    <div class="sticky top-0 z-10 flex gap-1 border-b border-slate-200 bg-white px-3 py-2">
        @foreach ([['all', 'All'], ['dinein', 'Dine In'], ['takeaway', 'Takeaway'], ['delivery', 'Delivery']] as [$k, $l])
            <button type="button" @click="kotFilter = '{{ $k }}'"
                    :class="kotFilter === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                    class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold">{{ $l }}</button>
        @endforeach
    </div>

    <div class="space-y-1.5 p-3">
        <template x-for="o in runningOrders.filter(o => kotFilter === 'all' || o.type === kotFilter)" :key="o.code">
            <button type="button" @click="loadOrder(o)"
                    :class="o.current ? 'border-slate-900 bg-slate-50' : 'border-slate-200 bg-white hover:border-slate-900'"
                    class="flex w-full items-center gap-2.5 rounded-md border p-2.5 text-left">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-slate-100 text-slate-600">
                    <template x-if="o.type === 'dinein'"><span><x-pos.icon name="table" class="h-4 w-4" /></span></template>
                    <template x-if="o.type === 'takeaway'"><span><x-pos.icon name="bag" class="h-4 w-4" /></span></template>
                    <template x-if="o.type === 'delivery'"><span><x-pos.icon name="scooter" class="h-4 w-4" /></span></template>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="flex items-center gap-1.5">
                        <span class="pos-num truncate text-[12.5px] font-bold text-slate-900" x-text="o.label"></span>
                        <span x-show="o.current" class="rounded bg-slate-900 px-1 text-[9px] font-bold uppercase tracking-wide text-white">Open here</span>
                    </p>
                    <p class="pos-num text-[10.5px] font-semibold text-slate-500">
                        <span x-text="o.code"></span>
                        <span x-show="o.waiter" x-text="' · ' + o.waiter"></span>
                        <span x-show="o.guests" x-text="' · ' + o.guests + ' pax'"></span>
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <p class="pos-num text-[13.5px] font-black text-slate-900" x-text="money(o.amount)"></p>
                    <p class="pos-num text-[10px] font-bold"
                       :class="o.mins > 45 ? 'text-rose-600' : o.mins > 25 ? 'text-amber-600' : 'text-slate-400'"
                       x-text="o.mins + ' min · ' + o.state"></p>
                </div>
            </button>
        </template>
    </div>

    <x-slot:footer>
        <button type="button" @click="back(); notify('New order — pick a table to begin')"
                class="flex h-11 w-full items-center justify-center gap-2 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
            <x-pos.icon name="plus" class="h-4 w-4" stroke="2.4" /> Start new order
            <kbd class="rounded bg-white/15 px-1 text-[9.5px]">F1</kbd>
        </button>
    </x-slot:footer>
</x-pos.dialog>
