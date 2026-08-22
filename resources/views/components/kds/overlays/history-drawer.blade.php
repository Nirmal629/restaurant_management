{{-- KitchenHistoryDrawer — completed tickets, read-only. --}}
<x-pos.dialog name="history" variant="drawer" width="max-w-md" title="Kitchen History" subtitle="Picked-up tickets, most recent first. Read-only.">
    <div class="border-b border-slate-200 bg-white p-3">
        <div class="relative">
            <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input x-model="historyQuery" data-autofocus placeholder="Search table / KOT / order…"
                   class="h-9 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
        </div>
    </div>

    <div class="space-y-1.5 p-3">
        <template x-for="t in filteredHistory" :key="t.kot">
            <div class="rounded-md border border-slate-200 bg-white p-2.5">
                <div class="flex items-center gap-2">
                    <span class="pos-num text-[13px] font-black text-slate-900" x-text="'KOT #' + t.kot"></span>
                    <x-kds.order-type-badge ticket="t" />
                    <span class="flex-1"></span>
                    <span class="rounded bg-slate-100 px-1.5 py-px text-[9px] font-black uppercase tracking-wide text-slate-600">Completed</span>
                </div>
                <p class="pos-num mt-1 text-[11px] font-semibold text-slate-500" x-text="orderLabel(t) + ' · ' + t.orderCode"></p>
                <p class="mt-1 text-[10.5px] font-semibold text-slate-400" x-text="'Prep ' + (prepMinutes(t) ?? '—') + ' min'"></p>
            </div>
        </template>

        <p x-show="!filteredHistory.length" class="py-14 text-center text-[12.5px] font-semibold text-slate-400">
            No completed tickets yet today.
        </p>
    </div>
</x-pos.dialog>
