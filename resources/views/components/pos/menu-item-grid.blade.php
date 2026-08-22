{{--
    MenuItemGrid / MenuItemCard.

    Tiles are text-first and 94–104px tall so ~25 items stay on screen at
    1366x768. Tap adds straight to the order; right-click / long-press opens the
    configurator for notes even on items with no required options.
--}}
<div class="pos-scroll bg-slate-100 p-2">
    <div class="pos-grid">
        <template x-for="item in visibleMenu" :key="item.id">
            <button type="button"
                    @click="tapItem(item)"
                    @contextmenu.prevent="item.stock !== 'out' && openConfig(item)"
                    :disabled="item.stock === 'out'"
                    :class="item.stock === 'out'
                        ? 'pos-hatch cursor-not-allowed border-slate-300 bg-slate-100'
                        : 'border-slate-200 bg-white hover:border-slate-900 hover:shadow-[0_1px_0_0_#0f172a] active:translate-y-px'"
                    class="pos-tile relative flex flex-col justify-between rounded-md border p-2 text-left">

                {{-- Name + diet mark ------------------------------------ --}}
                <div class="flex w-full items-start gap-1.5">
                    <span class="pos-clamp-2 min-w-0 flex-1 text-[12.5px] font-semibold leading-[1.25] text-slate-900"
                          :class="item.stock === 'out' && 'text-slate-500'"
                          x-text="item.name"></span>
                    <x-pos.diet-mark expr="item.diet" size="h-3.5 w-3.5" />
                </div>

                {{-- Stock / option flags -------------------------------- --}}
                <div class="flex w-full items-center gap-1">
                    <span x-show="item.stock === 'out'"
                          class="rounded border border-slate-400 bg-white px-1 py-px text-[9px] font-black uppercase tracking-[0.08em] text-slate-700">
                        Sold Out
                    </span>
                    <span x-show="item.stock === 'low'"
                          class="pos-num rounded border border-amber-400 bg-amber-50 px-1 py-px text-[9px] font-bold uppercase tracking-wide text-amber-800">
                        Low · <span x-text="item.left"></span> left
                    </span>
                    <span x-show="item.stock === 'in' && hasOptions(item)"
                          class="rounded border border-slate-300 bg-slate-50 px-1 py-px text-[9px] font-bold uppercase tracking-wide text-slate-600">
                        Options
                    </span>
                    <span x-show="item.stock === 'in' && !hasOptions(item) && item.fav"
                          class="rounded border border-slate-200 bg-slate-50 px-1 py-px text-[9px] font-bold uppercase tracking-wide text-slate-500">
                        Fav
                    </span>
                </div>

                {{-- Price + prep ---------------------------------------- --}}
                <div class="flex w-full items-end justify-between gap-1">
                    <span class="pos-num text-[15px] font-black leading-none tracking-tight"
                          :class="item.stock === 'out' ? 'text-slate-500' : 'text-slate-900'">
                        <span x-show="needsConfig(item)" class="text-[10px] font-bold text-slate-400">from </span><span x-text="money(item.price)"></span>
                    </span>
                    <span class="pos-num flex items-center gap-0.5 text-[10px] font-semibold text-slate-400">
                        <x-pos.icon name="clock" class="h-3 w-3" />
                        <span x-text="item.prep + 'm'"></span>
                    </span>
                </div>
            </button>
        </template>
    </div>

    {{-- Empty state ------------------------------------------------------ --}}
    <div x-show="!visibleMenu.length" class="flex flex-col items-center justify-center gap-2 py-16 text-center">
        <x-pos.icon name="search" class="h-7 w-7 text-slate-300" />
        <p class="text-[13px] font-semibold text-slate-500">No items match this filter</p>
        <button type="button" @click="clearFilters()"
                class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
            Reset filters
        </button>
    </div>
</div>
