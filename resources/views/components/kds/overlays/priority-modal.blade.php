{{-- Change Priority — reachable only from the KOT detail drawer's action list. --}}
<x-pos.dialog name="priority" width="max-w-sm" title="Change Priority">
    <div class="grid grid-cols-2 gap-1.5 p-4">
        <template x-for="p in ['normal', 'priority', 'rush', 'vip', 'waiting']" :key="p">
            <button type="button" @click="priorityDraft.value = p"
                    :class="priorityDraft.value === p ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                    class="h-11 rounded-md border text-[12px] font-bold" x-text="priorityLabel(p)"></button>
        </template>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmPriority()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Save</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
