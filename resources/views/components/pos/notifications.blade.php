{{--
    ReadyItemNotification + transient toast.

    Both float over the menu column and never take a layout slot, so the
    operator can keep punching while items come up at the pass.
--}}

{{-- Ready-at-the-pass stack: bottom-left of the menu area, max two shown --}}
<div class="pointer-events-none fixed bottom-3 z-40 flex w-[300px] flex-col gap-1.5"
     style="left: calc(var(--pos-rail-w) + 12px);">
    <template x-for="a in alerts.slice(0, 2)" :key="a.id">
        <div class="pointer-events-auto rounded-md border-2 border-emerald-500 bg-white shadow-lg">
            <div class="flex items-center gap-2 border-b border-emerald-100 bg-emerald-50 px-2.5 py-1">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                <span class="pos-num text-[10px] font-black uppercase tracking-[0.09em] text-emerald-800"
                      x-text="'Ready · Table ' + a.table"></span>
                <span class="flex-1"></span>
                <button type="button" @click="dismissAlert(a.id)"
                        class="grid h-5 w-5 place-items-center rounded text-emerald-700 hover:bg-emerald-100" aria-label="Dismiss">
                    <x-pos.icon name="x" class="h-3 w-3" stroke="2.4" />
                </button>
            </div>
            <div class="flex items-center gap-2 p-2.5">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[12.5px] font-bold text-slate-900" x-text="a.qty + '× ' + a.item"></p>
                    <p class="text-[10.5px] font-semibold text-slate-500" x-text="a.station"></p>
                </div>
                <button type="button" @click="open('kitchen')"
                        class="h-8 shrink-0 rounded-md border border-slate-300 px-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">View</button>
                <button type="button" @click="markServed(a)"
                        class="h-8 shrink-0 rounded-md bg-emerald-600 px-2.5 text-[11px] font-bold uppercase tracking-wide text-white hover:bg-emerald-500">Served</button>
            </div>
        </div>
    </template>

    <button type="button" x-show="alerts.length > 2" @click="open('kitchen')"
            class="pointer-events-auto rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-left text-[11px] font-bold text-slate-600 shadow">
        <span x-text="(alerts.length - 2) + ' more ready'"></span>
    </button>
</div>

{{-- Transient confirmation toast --}}
<div x-show="toast" x-cloak
     class="pointer-events-none fixed left-1/2 top-3 z-[100] -translate-x-1/2">
    <div :class="{
            success: 'border-emerald-500 bg-emerald-600 text-white',
            warn: 'border-amber-500 bg-amber-500 text-slate-950',
            info: 'border-slate-700 bg-slate-900 text-white',
         }[toast?.tone ?? 'info']"
         class="flex items-center gap-2 rounded-md border px-3.5 py-2 shadow-xl">
        <x-pos.icon name="check" class="h-4 w-4" stroke="2.6" />
        <span class="text-[12.5px] font-bold" x-text="toast?.message"></span>
    </div>
</div>
