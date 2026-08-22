{{--
    TableMap — scrollable floor plan; FloorLayoutEditor toolbar overlays when
    active. Intentionally NOT wrapped in its own pos-menu flex column — the
    caller (tables.blade.php) supplies that, so TableFilters can sit above
    this in the same column.
--}}
<div class="contents">

    {{-- FloorLayoutEditor toolbar --}}
    <div x-show="editLayout" x-cloak
         class="pos-dock flex flex-wrap items-center gap-2 border-b border-amber-300 bg-amber-50 px-3 py-2">
        <x-pos.icon name="grab" class="h-4 w-4 shrink-0 text-amber-700" />
        <p class="text-[11.5px] font-bold text-amber-900">
            Edit layout — drag any card to reorder it. Changes are visual only in this preview.
        </p>
        <span class="flex-1"></span>
        <div class="flex items-center gap-1.5">
            <input x-model="newSectionLabel" @keydown.enter="addSectionLabel()" placeholder="Add section label…"
                   class="h-8 w-40 rounded-md border border-amber-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
            <button type="button" @click="addSectionLabel()"
                    class="h-8 rounded-md border border-amber-400 bg-white px-2.5 text-[11px] font-bold text-amber-900 hover:border-slate-900">Add</button>
        </div>
        <button type="button" @click="toggleEditLayout()"
                class="h-8 rounded-md bg-slate-900 px-3 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Done</button>
    </div>

    <div class="pos-scroll bg-slate-100 p-2.5">

        {{-- Section labels for the active floor --}}
        <div x-show="activeFloor !== 'all' && (sectionLabels[activeFloor] || []).length"
             class="mb-2 flex flex-wrap gap-1.5">
            <template x-for="label in (sectionLabels[activeFloor] || [])" :key="label">
                <span class="flr-section-chip flex items-center gap-1 rounded-full border border-slate-300 bg-white px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-wide text-slate-500">
                    <x-pos.icon name="pin" class="h-3 w-3" />
                    <span x-text="label"></span>
                    <button type="button" x-show="editLayout" style="pointer-events:auto" @click="removeSectionLabel(activeFloor, label)"
                            class="ml-0.5 text-slate-400 hover:text-rose-600">
                        <x-pos.icon name="x" class="h-2.5 w-2.5" stroke="2.6" />
                    </button>
                </span>
            </template>
        </div>

        <div class="flr-map">
            <template x-for="c in cardGroups" :key="c.kind + c.label">
                <div><x-tables.table-card card="c" /></div>
            </template>
        </div>

        <div x-show="!cardGroups.length" class="flex flex-col items-center gap-2 py-16 text-center">
            <x-pos.icon name="table" class="h-7 w-7 text-slate-300" />
            <p class="text-[13px] font-semibold text-slate-500">No tables match this filter</p>
            <button type="button" @click="clearFilters()"
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Reset filters</button>
        </div>
    </div>

    {{-- TableUtilizationSummary — one compact footer row --}}
    <div class="pos-dock flex items-center gap-4 border-t border-slate-200 bg-white px-3 py-1.5">
        <span class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Seats</span>
        <span class="pos-num text-[11px] font-semibold text-slate-600">
            Total <span class="font-black text-slate-900" x-text="summary.totalCapacity"></span>
        </span>
        <span class="pos-num text-[11px] font-semibold text-slate-600">
            Occupied <span class="font-black text-sky-700" x-text="summary.guestsSeated"></span>
        </span>
        <span class="pos-num text-[11px] font-semibold text-slate-600">
            Available <span class="font-black text-emerald-700" x-text="summary.totalCapacity - summary.guestsSeated"></span>
        </span>
        <span class="flex-1"></span>
        <span class="pos-num text-[10.5px] font-semibold text-slate-400" x-text="cardGroups.length + ' card(s) on screen'"></span>
    </div>
</div>
