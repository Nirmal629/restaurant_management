{{-- MenuToolbar = MenuSearch + MenuFilter on one 40px row (two rows < 1200px) --}}
<div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-3 py-2">

    {{-- MenuSearch --}}
    <div class="relative min-w-[200px] flex-1 max-w-md">
        <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input x-ref="search" x-model="query" type="search" name="pos_menu_item_filter" autocomplete="off"
               autocorrect="off" autocapitalize="none" spellcheck="false" inputmode="search"
               @focus="if (query && query.includes('@')) query = ''"
               placeholder="Search item / code / SKU…"
               class="h-9 w-full rounded-md border border-slate-300 bg-white pl-8 pr-16 text-[13px] font-medium text-slate-900 placeholder:text-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900" />
        <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1">
            <button type="button" x-show="query" @click="query = ''" class="grid h-5 w-5 place-items-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Clear search">
                <x-pos.icon name="x" class="h-3.5 w-3.5" stroke="2.2" />
            </button>
            <kbd x-show="!query" class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[9.5px] font-bold text-slate-400">F2</kbd>
        </div>
    </div>

    {{-- MenuFilter --}}
    <div class="flex items-center gap-1">
        @foreach ([['all', 'All', null], ['veg', 'Veg', 'veg'], ['nonveg', 'Non-Veg', 'nonveg'], ['egg', 'Egg', 'egg']] as [$key, $label, $mark])
            <button type="button" @click="dietFilter = '{{ $key }}'"
                    :class="dietFilter === '{{ $key }}'
                        ? 'border-slate-900 bg-slate-900 text-white'
                        : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                    class="flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-[11.5px] font-bold">
                @if ($mark)
                    <span class="grid h-3 w-3 place-items-center rounded-[2px] border-[1.5px]"
                          :class="dietFilter === '{{ $key }}' ? 'border-white' : '{{ $mark === 'veg' ? 'border-emerald-600' : ($mark === 'nonveg' ? 'border-rose-600' : 'border-amber-500') }}'">
                        @if ($mark === 'veg')
                            <span class="h-1 w-1 rounded-full" :class="dietFilter === 'veg' ? 'bg-white' : 'bg-emerald-600'"></span>
                        @elseif ($mark === 'nonveg')
                            <span class="pos-tri h-1 w-1.5" :class="dietFilter === 'nonveg' ? 'bg-white' : 'bg-rose-600'"></span>
                        @else
                            <span class="h-1.5 w-1.5 rounded-full border" :class="dietFilter === 'egg' ? 'border-white' : 'border-amber-500'"></span>
                        @endif
                    </span>
                @endif
                {{ $label }}
            </button>
        @endforeach

        <button type="button" @click="availableOnly = !availableOnly"
                :class="availableOnly ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                class="flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-[11.5px] font-bold">
            <x-pos.icon name="check" class="h-3.5 w-3.5" stroke="2.4" />
            Available
        </button>
    </div>

    {{-- Result count doubles as the "filters are on" reset --}}
    <div class="ml-auto flex items-center gap-2">
        <span class="pos-num hidden text-[11px] font-semibold text-slate-500 lg:inline">
            <span x-text="visibleMenu.length"></span> of <span x-text="menu.length"></span> items
        </span>
        <button type="button" x-show="query || dietFilter !== 'all' || availableOnly || activeCat !== 'all'"
                @click="clearFilters()"
                class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900 hover:text-slate-900">
            <x-pos.icon name="refresh" class="h-3.5 w-3.5" />
            Reset
        </button>
    </div>
</div>
