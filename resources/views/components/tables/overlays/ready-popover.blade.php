{{-- ReadyItemsPopover — section 31. --}}
<x-pos.dialog name="ready" width="max-w-xs" title="Ready for pickup" :subtitle="null">
    <template x-if="activeCard">
        <div class="p-3">
            <p class="pos-num mb-2 text-[11px] font-bold text-slate-500" x-text="'Table ' + activeCard.label"></p>
            <div class="space-y-1.5">
                <template x-for="(it, i) in (activeCard.items || []).filter(x => x.state === 'READY')" :key="i">
                    <div class="flex items-center gap-2 rounded-md border border-emerald-300 bg-emerald-50 px-2.5 py-2">
                        <span class="pos-num text-[12px] font-bold text-slate-900" x-text="it.qty + '×'"></span>
                        <span class="flex-1 truncate text-[12.5px] font-semibold text-slate-800" x-text="it.name"></span>
                        <span class="rounded border border-emerald-500 bg-white px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-emerald-700">Ready</span>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="open('details')"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Open Order</button>
            <button type="button" @click="notify('Ready items marked served'); back()"
                    class="h-10 flex-1 rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">Mark Served</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
