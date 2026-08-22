{{-- Small dialogs: waiter, order notes, transfer, merge, shortcut sheet --}}

<x-pos.dialog name="waiter" width="max-w-sm" title="Change waiter">
    <div class="grid grid-cols-2 gap-1.5 p-3">
        <template x-for="w in waiters" :key="w">
            <button type="button" @click="order.waiter = w; back(); notify(w + ' is now serving ' + order.table)"
                    :class="order.waiter === w ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-900'"
                    class="flex h-12 items-center gap-2 rounded-md border px-3">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-[12px] font-bold text-slate-700"
                      x-text="w.charAt(0)"></span>
                <span class="text-[13px] font-bold" x-text="w"></span>
            </button>
        </template>
    </div>
</x-pos.dialog>


<x-pos.dialog name="notes" width="max-w-md" title="Order notes"
              subtitle="Visible to the kitchen and printed on the KOT header.">
    <div class="space-y-2 p-3">
        <div class="flex flex-wrap gap-1.5">
            @foreach (['Birthday table', 'Rush — guest in a hurry', 'Allergy: nuts', 'Serve course by course', 'VIP guest'] as $preset)
                <button type="button" @click="order.notes = (order.notes ? order.notes + ', ' : '') + '{{ $preset }}'"
                        class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[11.5px] font-bold text-slate-600 hover:border-slate-900">{{ $preset }}</button>
            @endforeach
        </div>
        <textarea x-model="order.notes" data-autofocus rows="4" placeholder="Note for this order…"
                  class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[13px] font-medium focus:border-slate-900 focus:outline-none"></textarea>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="order.notes = ''"
                    class="h-10 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-slate-600 hover:border-rose-500 hover:text-rose-600">Clear</button>
            <span class="flex-1"></span>
            <button type="button" @click="back()"
                    class="h-10 rounded-md bg-slate-900 px-5 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Save note</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


<x-pos.dialog name="transfer" width="max-w-md" title="Transfer table"
              subtitle="Move this entire order — items, KOTs and charges — to another table.">
    <div class="space-y-2 p-3">
        <div class="flex items-center gap-2 rounded-md border border-slate-300 bg-slate-50 p-3">
            <div class="flex-1 text-center">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">From</p>
                <p class="pos-num text-[20px] font-black text-slate-900" x-text="order.table"></p>
            </div>
            <x-pos.icon name="chevron-right" class="h-5 w-5 text-slate-400" />
            <div class="flex-1 text-center">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">To</p>
                <p class="pos-num text-[20px] font-black text-slate-300">—</p>
            </div>
        </div>
        <p class="text-[11.5px] font-semibold text-slate-500">Pick a destination table:</p>
        <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));">
            <template x-for="t in tables.filter(t => t.status === 'available')" :key="t.id">
                <button type="button" @click="back(); notify('Order moved to ' + t.id)"
                        class="rounded-md border-2 border-emerald-300 bg-emerald-50 py-2 hover:border-emerald-600">
                    <span class="pos-num block text-[15px] font-black text-slate-900" x-text="t.id"></span>
                    <span class="pos-num block text-[10px] font-semibold text-slate-500" x-text="t.seats + ' seats'"></span>
                </button>
            </template>
        </div>
    </div>
</x-pos.dialog>


<x-pos.dialog name="merge" width="max-w-md" title="Merge table"
              subtitle="Combine another running order into this one. Both KOT histories are preserved.">
    <div class="space-y-1.5 p-3">
        <template x-for="o in runningOrders.filter(o => o.type === 'dinein' && !o.current)" :key="o.code">
            <button type="button" @click="back(); notify(o.label + ' merged into ' + order.table)"
                    class="flex w-full items-center gap-2.5 rounded-md border border-slate-200 bg-white p-2.5 text-left hover:border-slate-900">
                <div class="min-w-0 flex-1">
                    <p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="o.label"></p>
                    <p class="pos-num text-[10.5px] font-semibold text-slate-500" x-text="o.code + ' · ' + o.waiter + ' · ' + o.mins + ' min'"></p>
                </div>
                <span class="pos-num text-[13.5px] font-black text-slate-900" x-text="money(o.amount)"></span>
            </button>
        </template>
    </div>
</x-pos.dialog>


<x-pos.dialog name="shortcuts" width="max-w-md" title="Keyboard shortcuts"
              subtitle="Every shortcut has a touch equivalent — the keyboard is optional.">
    <div class="p-3">
        <dl class="divide-y divide-slate-100 rounded-md border border-slate-200">
            <template x-for="s in shortcuts" :key="s.keys">
                <div class="flex items-center gap-3 px-3 py-2">
                    <dt><kbd class="pos-num inline-grid h-6 min-w-[36px] place-items-center rounded border border-slate-300 bg-slate-50 px-1.5 text-[11px] font-bold text-slate-700" x-text="s.keys"></kbd></dt>
                    <dd class="text-[12.5px] font-semibold text-slate-700" x-text="s.label"></dd>
                </div>
            </template>
        </dl>
        <p class="mt-2.5 rounded border border-slate-200 bg-slate-50 p-2 text-[11px] leading-snug text-slate-500">
            Right-click (or long-press) any menu tile to open the item configurator, even for items with no
            required options — that is how you attach a kitchen instruction to a plain item.
        </p>
    </div>
</x-pos.dialog>
