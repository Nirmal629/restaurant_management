{{-- ItemUnavailableModal — section 34. Never silently removes the item. --}}
<x-pos.dialog name="unavailable" width="max-w-sm" title="Mark Item Unavailable">
    <div class="space-y-3 p-4">
        <p class="text-[13px] font-bold text-slate-900" x-text="ticket(unavailableDraft.kot)?.items.find(i => i.uid === unavailableDraft.uid)?.name"></p>
        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason</p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="r in unavailableReasons" :key="r">
                    <button type="button" @click="unavailableDraft.reason = r"
                            :class="unavailableDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                            class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                </template>
            </div>
        </div>
        <p class="rounded border border-amber-200 bg-amber-50 p-2 text-[11px] leading-snug text-amber-800">
            The item stays visible on the ticket marked <span class="font-bold">Unavailable</span> — it will not
            silently disappear. POS and the assigned waiter are notified.
        </p>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmUnavailable()" :disabled="!unavailableDraft.reason"
                    class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Mark Unavailable</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
