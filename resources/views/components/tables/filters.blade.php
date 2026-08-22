{{-- TableFilters — search + waiter/capacity/kitchen-ready, kept to one row. --}}
<div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-3 py-2">

    <div class="relative min-w-[190px] max-w-xs flex-1">
        <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input x-model="query" type="text" placeholder="Search table / order / customer…"
               class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
    </div>

    <select x-model="waiterFilter"
            class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
        <option value="all">All Waiters</option>
        <template x-for="w in waiterStats" :key="w.name">
            <option :value="w.name" x-text="w.name + ' — ' + w.tables + ' table(s)'"></option>
        </template>
    </select>

    <div class="flex items-center gap-1">
        @foreach ([['2-2', '2'], ['4-4', '4'], ['6-6', '6'], ['8-', '8+']] as [$val, $label])
            <button type="button" @click="capacityFilter = capacityFilter === '{{ $val }}' ? 'all' : '{{ $val }}'"
                    :class="capacityFilter === '{{ $val }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                    class="h-8 rounded-md border px-2.5 text-[11.5px] font-bold">{{ $label }} seats</button>
        @endforeach
    </div>

    <button type="button" @click="kitchenReadyOnly = !kitchenReadyOnly"
            :class="kitchenReadyOnly ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
            class="flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-[11.5px] font-bold">
        <x-pos.icon name="chef" class="h-3.5 w-3.5" /> Kitchen Ready
    </button>

    <span class="ml-auto flex items-center gap-2">
        <span class="pos-num hidden text-[11px] font-semibold text-slate-500 lg:inline">
            <span x-text="cardGroups.length"></span> shown
        </span>
        <button type="button" x-show="query || waiterFilter !== 'all' || capacityFilter !== 'all' || kitchenReadyOnly || statusFilter !== 'all'"
                @click="clearFilters()"
                class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900">
            <x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset
        </button>
    </span>
</div>
