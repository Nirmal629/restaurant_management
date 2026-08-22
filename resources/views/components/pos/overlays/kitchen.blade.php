{{-- KotHistoryDrawer + kitchen ready queue --}}

<x-pos.dialog name="kot" variant="drawer" width="max-w-md" title="KOT history"
              subtitle="Every round dispatched on this order.">
    <div class="space-y-2 p-3">
        <template x-for="k in [...kotHistory].reverse()" :key="k.kot">
            <div class="rounded-md border border-slate-200 bg-white">
                <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-50 px-2.5 py-1.5">
                    <span class="pos-num rounded bg-slate-900 px-1.5 py-px text-[10.5px] font-bold text-white" x-text="'KOT #' + k.kot"></span>
                    <span class="pos-num text-[10.5px] font-semibold text-slate-500" x-text="'Round ' + k.round + ' · ' + k.sentAt + ' · ' + k.by"></span>
                    <span class="flex-1"></span>
                    <button type="button" @click="notify('KOT #' + k.kot + ' reprinted')"
                            class="flex items-center gap-1 rounded border border-slate-300 px-1.5 py-1 text-[10.5px] font-bold text-slate-600 hover:border-slate-900 hover:text-slate-900">
                        <x-pos.icon name="printer" class="h-3.5 w-3.5" /> Reprint
                    </button>
                </div>
                <div class="divide-y divide-slate-100">
                    <template x-for="(l, i) in k.lines" :key="i">
                        <div class="flex items-start gap-2 px-2.5 py-1.5">
                            <span class="pos-num shrink-0 text-[12px] font-bold text-slate-900" x-text="l.qty + '×'"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-semibold leading-tight text-slate-900" x-text="l.name"></p>
                                <p x-show="l.note" class="text-[10.5px] italic text-slate-500" x-text="l.note"></p>
                            </div>
                            <span :class="statusClass(l.state.toLowerCase())"
                                  class="shrink-0 rounded border px-1.5 py-px text-[9px] font-bold uppercase tracking-wide" x-text="l.state"></span>
                        </div>
                    </template>
                </div>
                <p class="border-t border-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-400" x-text="'Printed to ' + k.printer"></p>
            </div>
        </template>
    </div>
</x-pos.dialog>


<x-pos.dialog name="kitchen" variant="drawer" width="max-w-md" title="Kitchen"
              subtitle="Live station load and items waiting to be picked up.">

    <div class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200 bg-white">
        @foreach ([['new', 'New'], ['prep', 'Preparing'], ['ready', 'Ready']] as [$k, $l])
            <div class="px-3 py-2.5 {{ $k === 'ready' ? 'bg-emerald-50' : '' }}">
                <p class="text-[9.5px] font-black uppercase tracking-[0.09em] text-slate-400">{{ $l }}</p>
                <p class="pos-num text-[22px] font-black leading-tight {{ $k === 'ready' ? 'text-emerald-700' : 'text-slate-900' }}"
                   x-text="kitchen.{{ $k }}"></p>
            </div>
        @endforeach
    </div>

    <div class="p-3">
        <p class="mb-2 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Ready for pickup</p>

        <div class="space-y-1.5">
            <template x-for="a in alerts" :key="a.id">
                <div class="flex items-center gap-2.5 rounded-md border-2 border-emerald-400 bg-emerald-50 p-2.5">
                    <div class="min-w-0 flex-1">
                        <p class="pos-num text-[10.5px] font-black uppercase tracking-wide text-emerald-800" x-text="'Table ' + a.table + ' · ' + a.station"></p>
                        <p class="truncate text-[13px] font-bold text-slate-900" x-text="a.qty + '× ' + a.item"></p>
                    </div>
                    <button type="button" @click="dismissAlert(a.id)"
                            class="h-8 rounded-md border border-emerald-600 bg-white px-2.5 text-[11px] font-bold uppercase tracking-wide text-emerald-800">View</button>
                    <button type="button" @click="markServed(a)"
                            class="h-8 rounded-md bg-emerald-600 px-2.5 text-[11px] font-bold uppercase tracking-wide text-white hover:bg-emerald-500">Mark served</button>
                </div>
            </template>

            <p x-show="!alerts.length" class="rounded-md border border-dashed border-slate-300 bg-slate-50 py-10 text-center text-[12.5px] font-semibold text-slate-500">
                Nothing waiting at the pass.
            </p>
        </div>

        <p class="mt-3 rounded border border-slate-200 bg-slate-50 p-2 text-[11px] leading-snug text-slate-500">
            Station updates arrive over the live channel once the KDS backend is wired in. The UI is already
            non-blocking — notifications never interrupt punching.
        </p>
    </div>
</x-pos.dialog>
