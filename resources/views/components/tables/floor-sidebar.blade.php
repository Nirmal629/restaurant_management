{{--
    FloorSidebar (>=1200px, vertical list with per-floor occupancy) collapses
    into FloorTabs (a horizontal strip) below 1200px — same responsive pattern
    as the POS category rail.
--}}
<aside class="hidden shrink-0 border-r border-slate-200 bg-white min-[1200px]:flex min-[1200px]:flex-col"
       style="width: var(--flr-rail-w);">
    <nav class="pos-scroll p-1.5" aria-label="Floors">
        <button type="button" @click="activeFloor = 'all'"
                :class="activeFloor === 'all' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'"
                class="mb-1 flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left">
            <x-pos.icon name="grid" class="h-4 w-4 shrink-0" />
            <span class="flex-1 text-[12.5px] font-bold">All Floors</span>
            <span :class="activeFloor === 'all' ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500'"
                  class="pos-num rounded px-1.5 py-px text-[10px] font-bold" x-text="summary.total"></span>
        </button>

        <div class="my-1.5 border-t border-slate-200"></div>

        <template x-for="f in floors" :key="f.key">
            <button type="button" @click="activeFloor = f.key"
                    :class="activeFloor === f.key ? 'border-slate-900 bg-slate-50' : 'border-transparent hover:bg-slate-50'"
                    class="mb-1 w-full rounded-md border px-2.5 py-2 text-left">
                <p class="truncate text-[12.5px] font-bold text-slate-900" x-text="f.label"></p>
                <p class="pos-num mt-0.5 text-[10.5px] font-semibold text-slate-500">
                    <span x-text="floorTableCount(f.key) + ' tables'"></span>
                    <span class="text-sky-600" x-text="' · ' + floorOccupiedCount(f.key) + ' occupied'"></span>
                </p>
            </button>
        </template>
    </nav>

    <div class="pos-dock border-t border-slate-200 p-1.5">
        <button type="button" @click="openAddFloor()"
                class="flex w-full items-center justify-center gap-1.5 rounded-md border border-dashed border-slate-300 py-1.5 text-[11px] font-bold uppercase tracking-wide text-slate-500 hover:border-slate-900 hover:text-slate-900">
            <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.2" /> Add Floor
        </button>
    </div>
</aside>

{{-- FloorTabs: 1024px landscape and below --}}
<div class="pos-dock flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-3 py-2 pos-no-scrollbar min-[1200px]:hidden">
    <button type="button" @click="activeFloor = 'all'"
            :class="activeFloor === 'all' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
            class="shrink-0 rounded-md border px-3 py-1.5 text-[12px] font-bold">All Floors</button>
    <template x-for="f in floors" :key="f.key">
        <button type="button" @click="activeFloor = f.key"
                :class="activeFloor === f.key ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                class="flex shrink-0 items-center gap-1.5 rounded-md border px-3 py-1.5 text-[12px] font-bold">
            <span x-text="f.label"></span>
            <span class="pos-num rounded px-1 text-[10px]" :class="activeFloor === f.key ? 'bg-white/20' : 'bg-slate-100 text-slate-500'"
                  x-text="floorTableCount(f.key)"></span>
        </button>
    </template>
</div>
