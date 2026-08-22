{{-- ChangeWaiterModal — section 17. Load is shown so nobody gets overloaded. --}}
<x-pos.dialog name="waiter" width="max-w-sm" title="Change Waiter">
    <div class="p-4">
        <p class="mb-2 text-[11px] font-semibold text-slate-500">
            Current: <span class="font-bold text-slate-900" x-text="table(waiterDraft.tableId)?.waiter || '—'"></span>
        </p>
        <div class="space-y-1.5">
            <template x-for="w in waiterStats" :key="w.name">
                <button type="button" @click="waiterDraft.waiter = w.name"
                        :class="waiterDraft.waiter === w.name ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                        class="flex w-full items-center gap-2.5 rounded-md border px-3 py-2.5">
                    <span :class="waiterDraft.waiter === w.name ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-700'"
                          class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-[12px] font-bold" x-text="w.name.charAt(0)"></span>
                    <span class="flex-1 text-left">
                        <span class="block text-[13px] font-bold" x-text="w.name"></span>
                    </span>
                    <span class="pos-num rounded px-1.5 py-0.5 text-[10px] font-bold"
                          :class="waiterDraft.waiter === w.name ? 'bg-white/15' : 'bg-slate-100 text-slate-600'"
                          x-text="w.tables + ' Tables'"></span>
                </button>
            </template>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmWaiterChange()"
                    class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Assign</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
