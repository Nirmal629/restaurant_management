{{-- KdsFilterBar + KdsSearch — status filtering already lives in the summary chips above. --}}
<div class="pos-dock kds-hide-secondary flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-3 py-2">

    <div class="relative min-w-[190px] max-w-xs flex-1">
        <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input x-model="query" type="text" placeholder="Search table / KOT / item…"
               class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
    </div>

    {{-- Secondary filters live behind one popover so the bar stays a single row. --}}
    <div class="relative" x-data @click.outside="filtersOpen = false">
        <button type="button" @click="filtersOpen = !filtersOpen"
                :class="(typeFilter !== 'all' || waitFilter !== 'all' || priorityFilter !== 'all') ? 'border-slate-900 bg-slate-50' : 'border-slate-300 bg-white'"
                class="flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
            <x-pos.icon name="filter" class="h-3.5 w-3.5" /> Filters
        </button>
        <div x-show="filtersOpen" x-cloak x-transition.origin.top.left
             class="absolute left-0 top-9 z-30 w-72 space-y-3 rounded-md border border-slate-300 bg-white p-3 shadow-2xl">

            <div>
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.08em] text-slate-400">Order Type</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['all' => 'All', 'dinein' => 'Dine In', 'takeaway' => 'Takeaway', 'delivery' => 'Delivery'] as $val => $label)
                        <button type="button" @click="typeFilter = '{{ $val }}'"
                                :class="typeFilter === '{{ $val }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                                class="rounded-md border px-2 py-1 text-[11px] font-bold">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.08em] text-slate-400">Wait Time</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['all' => 'Any', 'lt15' => '< 15 min', '15-25' => '15–25 min', '25+' => '25+ min'] as $val => $label)
                        <button type="button" @click="waitFilter = '{{ $val }}'"
                                :class="waitFilter === '{{ $val }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                                class="rounded-md border px-2 py-1 text-[11px] font-bold">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.08em] text-slate-400">Priority</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['all' => 'All', 'normal' => 'Normal', 'priority' => 'Priority', 'rush' => 'Rush'] as $val => $label)
                        <button type="button" @click="priorityFilter = '{{ $val }}'"
                                :class="priorityFilter === '{{ $val }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                                class="rounded-md border px-2 py-1 text-[11px] font-bold">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <button type="button" @click="clearFilters(); filtersOpen = false"
                    class="flex h-8 w-full items-center justify-center gap-1.5 rounded-md border border-slate-300 text-[11px] font-bold text-slate-600 hover:border-slate-900">
                <x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset all
            </button>
        </div>
    </div>

    {{-- Sort — manager-facing; chef-only stations rarely need to touch this. --}}
    <select x-model="sortMode"
            class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
        <option value="oldest">Sort: Oldest first</option>
        <option value="newest">Sort: Newest first</option>
        <option value="priority">Sort: Priority first</option>
        <option value="table">Sort: Table number</option>
    </select>

    <span class="ml-auto flex items-center gap-2">
        <button type="button" x-show="query || typeFilter !== 'all' || waitFilter !== 'all' || priorityFilter !== 'all' || statusFilter !== 'all'"
                @click="clearFilters()"
                class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900">
            <x-pos.icon name="x" class="h-3.5 w-3.5" stroke="2.2" /> Clear
        </button>
    </span>
</div>
