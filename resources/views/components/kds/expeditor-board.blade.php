{{--
    ExpeditorBoard — table/order-oriented, cross-station progress. This is the
    coordination view (section 17-18): one card per order regardless of which
    stations its items are routed to, rather than one column per status.
--}}
<div class="pos-scroll bg-slate-100 p-2.5">
    <div class="grid gap-2.5" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
        <template x-for="g in expeditorGroups" :key="g.orderCode">
            <div x-data="{ expanded: false }"
                 class="rounded-md border-2 bg-white p-3 shadow-sm"
                 :class="g.allReady ? 'border-emerald-400' : 'border-slate-300'">

                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="kds-card-title pos-num font-black text-slate-900" x-text="g.table ? g.table : (g.orderType === 'takeaway' ? 'Token #' + g.token : 'Order #' + g.token)"></p>
                        <p class="pos-num mt-0.5 text-[10.5px] font-semibold text-slate-500" x-text="g.orderCode + (g.waiter ? ' · ' + g.waiter : '')"></p>
                    </div>
                    <span class="pos-num text-[13px] font-black" :class="g.allReady ? 'text-emerald-700' : 'text-slate-500'"
                          x-text="Math.max(0, Math.round((now - g.placedAt) / 60000)) + 'm'"></span>
                </div>

                <div class="mt-2 flex items-center gap-2">
                    <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-700" x-text="g.totalCount + ' Items'"></span>
                    <span class="rounded px-2 py-1 text-[11px] font-black" :class="g.allReady ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700'" x-text="g.readyCount + ' Ready'"></span>
                    <span x-show="g.totalCount - g.readyCount > 0" class="rounded bg-orange-50 px-2 py-1 text-[11px] font-black text-orange-700" x-text="(g.totalCount - g.readyCount) + ' Preparing'"></span>
                </div>

                {{-- Per-station breakdown --}}
                <div class="mt-2 space-y-1">
                    <template x-for="s in g.stationBreakdown" :key="s.key">
                        <div class="flex items-center justify-between rounded border border-slate-200 bg-slate-50 px-2 py-1">
                            <span class="text-[11px] font-bold text-slate-700" x-text="s.label"></span>
                            <span class="pos-num text-[11px] font-black" :class="s.ready === s.total ? 'text-emerald-700' : 'text-slate-600'" x-text="s.ready + '/' + s.total + ' Ready'"></span>
                        </div>
                    </template>
                </div>

                {{-- Missing items, expanded on demand --}}
                <div x-show="expanded" class="mt-2 space-y-1 border-t border-slate-100 pt-2">
                    <template x-for="i in g.items" :key="i.uid">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-semibold text-slate-700" x-text="i.qty + '× ' + i.name"></span>
                            <span class="font-bold" :class="i.status === 'ready' ? 'text-emerald-700' : 'text-slate-400'" x-text="itemStatusLabel(i.status)"></span>
                        </div>
                    </template>
                </div>

                <div class="mt-2.5 grid grid-cols-2 gap-1.5">
                    <button type="button" @click="expanded = !expanded"
                            class="h-9 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900"
                            x-text="expanded ? 'Hide Items' : 'View Items'"></button>
                    <button type="button" @click="markGroupReadyForService(g)" :disabled="g.allReady"
                            class="h-9 rounded-md bg-emerald-600 text-[11px] font-black uppercase tracking-wide text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-300">
                        Mark Ready
                    </button>
                </div>
            </div>
        </template>

        <p x-show="!expeditorGroups.length" class="col-span-full py-16 text-center text-[13px] font-semibold text-slate-400">No active orders match this filter.</p>
    </div>
</div>
