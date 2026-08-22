{{--
    TableStatusSummary — compact clickable counters. Clicking a chip toggles
    that status as the active filter; clicking it again clears the filter.
--}}
<div class="pos-infobar z-20 flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-3 pos-no-scrollbar">

    <button type="button" @click="statusFilter = 'all'"
            :class="statusFilter === 'all' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400'"
            class="flex h-8 shrink-0 items-center gap-1.5 rounded-md border px-2.5">
        <span class="text-[10px] font-bold uppercase tracking-wide">Total</span>
        <span class="pos-num text-[13px] font-black" x-text="summary.total"></span>
    </button>

    <div class="h-5 w-px shrink-0 bg-slate-200"></div>

    @foreach ([
        ['available', 'Available', 'bg-emerald-500'],
        ['occupied', 'Occupied', 'bg-sky-500'],
        ['reserved', 'Reserved', 'bg-violet-500'],
        ['billing', 'Billing', 'bg-amber-500'],
        ['cleaning', 'Cleaning', 'bg-slate-400'],
        ['disabled', 'Disabled', 'bg-rose-500'],
    ] as [$key, $label, $dot])
        <button type="button" @click="toggleStatusFilter('{{ $key }}')"
                :class="statusFilter === '{{ $key }}' ? 'border-slate-900 bg-slate-50' : 'border-transparent bg-white hover:border-slate-200'"
                class="flex h-8 shrink-0 items-center gap-1.5 rounded-md border px-2">
            <span class="h-2 w-2 shrink-0 rounded-full {{ $dot }}"></span>
            <span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-600">{{ $label }}</span>
            <span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.{{ $key }}"></span>
        </button>
    @endforeach

    <span class="flex-1"></span>

    <div class="flex shrink-0 items-center gap-3 border-l border-slate-200 pl-3">
        <div class="text-right leading-tight">
            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Guests Seated</p>
            <p class="pos-num text-[13px] font-black text-slate-900" x-text="summary.guestsSeated"></p>
        </div>
        <div class="text-right leading-tight">
            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Running Revenue</p>
            <p class="pos-num text-[13px] font-black text-slate-900" x-text="money(summary.revenue)"></p>
        </div>
    </div>
</div>
