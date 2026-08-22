{{-- NewKotNotification — non-blocking, auto-dismisses, never covers the board. --}}
<div class="pointer-events-none fixed bottom-3 right-3 z-40 flex w-72 flex-col gap-1.5">
    <template x-for="n in notifications" :key="n.id">
        <div class="pointer-events-auto overflow-hidden rounded-md border-2 border-slate-900 bg-white shadow-xl">
            <div class="flex items-center gap-1.5 bg-slate-900 px-2.5 py-1">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-400 kds-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.09em] text-white">New KOT</span>
                <span class="flex-1"></span>
                <button type="button" @click="dismissAlert(n.id)" class="grid h-5 w-5 place-items-center rounded text-slate-400 hover:bg-slate-800 hover:text-white" aria-label="Dismiss">
                    <x-pos.icon name="x" class="h-3 w-3" stroke="2.4" />
                </button>
            </div>
            <div class="flex items-center gap-2 p-2.5">
                <div class="min-w-0 flex-1">
                    <p class="pos-num truncate text-[15px] font-black text-slate-900" x-text="n.label"></p>
                    <p class="text-[10.5px] font-semibold text-slate-500" x-text="'KOT #' + n.kot + ' · ' + n.count + ' item(s)'"></p>
                </div>
                <button type="button" @click="dismissAlert(n.id)"
                        class="h-8 shrink-0 rounded-md bg-slate-900 px-2.5 text-[10.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">View</button>
            </div>
        </div>
    </template>
</div>
