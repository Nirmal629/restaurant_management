{{-- TableSelectorModal — floors as tabs, tables as status cards, scrolls internally --}}
<x-pos.dialog name="table" title="Select Table" width="max-w-5xl"
              subtitle="Tap a table to attach this order. Tables being cleaned cannot be seated.">

    <x-slot:headerActions>
        <div class="flex items-center gap-2">
            <div class="relative">
                <x-pos.icon name="search" class="pointer-events-none absolute left-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input x-model="tableQuery" data-autofocus placeholder="Search table…"
                       class="h-8 w-40 rounded-md border border-slate-300 bg-white pl-7 pr-2 text-[12px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
            <button type="button" @click="tableAvailableOnly = !tableAvailableOnly"
                    :class="tableAvailableOnly ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                    class="h-8 rounded-md border px-2.5 text-[11.5px] font-bold">Available only</button>
        </div>
    </x-slot:headerActions>

    {{-- Floor tabs --}}
    <div class="sticky top-0 z-10 flex items-center gap-1 border-b border-slate-200 bg-white px-3 py-2">
        <template x-for="f in floors" :key="f.key">
            <button type="button" @click="tableFloor = f.key"
                    :class="tableFloor === f.key ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                    class="flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-[12px] font-bold">
                <span x-text="f.label"></span>
                <span class="pos-num rounded px-1 text-[10px]"
                      :class="tableFloor === f.key ? 'bg-white/15' : 'bg-slate-100 text-slate-500'"
                      x-text="tables.filter(t => t.floor === f.key && t.status === 'available').length + ' free'"></span>
            </button>
        </template>

        <span class="flex-1"></span>

        {{-- Legend: word first, colour second --}}
        <div class="hidden items-center gap-2 lg:flex">
            @foreach ([['Available', 'bg-emerald-400'], ['Reserved', 'bg-violet-400'], ['Occupied', 'bg-sky-400'], ['Billing', 'bg-amber-400'], ['Cleaning', 'bg-slate-400']] as [$l, $c])
                <span class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                    <span class="h-2 w-2 rounded-sm {{ $c }}"></span>{{ $l }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="grid gap-2 p-3" style="grid-template-columns: repeat(auto-fill, minmax(132px, 1fr));">
        <template x-for="t in visibleTables" :key="t.id">
            <button type="button" @click="pickTable(t)" :disabled="t.status === 'cleaning'"
                    :class="[tableStatusClass(t.status), t.id === order.table ? 'ring-2 ring-slate-900 ring-offset-1' : '', t.status === 'cleaning' ? 'cursor-not-allowed' : '']"
                    class="flex h-[86px] flex-col justify-between rounded-md border-2 p-2 text-left">
                <div class="flex items-start justify-between">
                    <span class="pos-num text-[17px] font-black leading-none tracking-tight text-slate-900" x-text="t.id"></span>
                    <span class="pos-num flex items-center gap-0.5 text-[10px] font-bold text-slate-500">
                        <x-pos.icon name="users" class="h-3 w-3" /><span x-text="t.seats"></span>
                    </span>
                </div>

                <span class="text-[9.5px] font-black uppercase tracking-[0.09em] text-slate-700" x-text="t.status"></span>

                <span class="pos-num text-[11px] font-semibold text-slate-600">
                    <span x-show="t.amount" x-text="money(t.amount) + ' · ' + t.since + 'm'"></span>
                    <span x-show="t.status === 'reserved'" x-text="t.reservedFor + ' · ' + (t.guestName || '')" class="block truncate"></span>
                    <span x-show="t.status === 'available'" class="text-slate-400">Ready to seat</span>
                    <span x-show="t.status === 'cleaning'" class="text-slate-400">Being cleaned</span>
                </span>
            </button>
        </template>

        <p x-show="!visibleTables.length" class="col-span-full py-12 text-center text-[13px] font-semibold text-slate-400">
            No tables match this filter.
        </p>
    </div>

    <x-slot:footer>
        <div class="flex items-center gap-2">
            <p class="flex-1 text-[11.5px] font-medium text-slate-500">
                Current: <span class="pos-num font-bold text-slate-900" x-text="order.table + ' · ' + order.floor"></span>
            </p>
            <button type="button" @click="runMore('transfer')" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-slate-700 hover:border-slate-900">Transfer table</button>
            <button type="button" @click="runMore('merge')" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-slate-700 hover:border-slate-900">Merge table</button>
            <button type="button" @click="back()" class="h-9 rounded-md bg-slate-900 px-4 text-[12px] font-bold text-white hover:bg-slate-800">Done</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
