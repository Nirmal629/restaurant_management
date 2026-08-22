{{--
    KitchenSummaryBar — status counts double as filter chips (click to
    isolate, click again to clear), plus two manager-facing timing metrics.
--}}
<div class="pos-infobar z-20 flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-3 pos-no-scrollbar">

    @foreach ([
        ['new', 'New', 'bg-slate-400'],
        ['accepted', 'Accepted', 'bg-sky-500'],
        ['preparing', 'Preparing', 'bg-orange-500'],
        ['ready', 'Ready', 'bg-emerald-500'],
    ] as [$key, $label, $dot])
        <button type="button" @click="statusFilter = statusFilter === '{{ $key }}' ? 'all' : '{{ $key }}'"
                :class="statusFilter === '{{ $key }}' ? 'border-slate-900 bg-slate-50' : 'border-transparent bg-white hover:border-slate-200'"
                class="flex h-8 shrink-0 items-center gap-1.5 rounded-md border px-2">
            <span class="h-2 w-2 shrink-0 rounded-full {{ $dot }}"></span>
            <span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-600">{{ $label }}</span>
            <span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.{{ $key }}"></span>
        </button>
    @endforeach

    <button type="button" @click="statusFilter = statusFilter === 'delayed' ? 'all' : 'delayed'"
            :class="statusFilter === 'delayed' ? 'border-rose-500 bg-rose-50' : 'border-transparent bg-white hover:border-rose-200'"
            class="flex h-8 shrink-0 items-center gap-1.5 rounded-md border px-2">
        <x-pos.icon name="alert" class="h-3.5 w-3.5 text-rose-600" />
        <span class="text-[10.5px] font-bold uppercase tracking-wide text-rose-700">Delayed</span>
        <span class="pos-num text-[13px] font-black text-rose-700" x-text="summary.delayed"></span>
    </button>

    <span class="flex-1"></span>

    <div class="flex shrink-0 items-center gap-3 border-l border-slate-200 pl-3">
        <div class="text-right leading-tight">
            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Average Prep</p>
            <p class="pos-num text-[13px] font-black text-slate-900" x-text="summary.avgPrep + 'm'"></p>
        </div>
        <div class="text-right leading-tight">
            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Oldest Ticket</p>
            <p class="pos-num text-[13px] font-black" :class="summary.oldest >= config.criticalMinutes ? 'text-rose-600' : 'text-slate-900'" x-text="summary.oldest + 'm'"></p>
        </div>
        <button type="button" @click="openHistory()"
                class="flex h-8 items-center gap-1.5 rounded-md border border-slate-300 px-2.5 text-[10.5px] font-bold text-slate-600 hover:border-slate-900 hover:text-slate-900">
            <x-pos.icon name="receipt" class="h-3.5 w-3.5" /> History
        </button>
    </div>
</div>
