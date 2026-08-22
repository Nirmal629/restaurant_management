{{-- StartTableModal — section 11. --}}
<x-pos.dialog name="start" width="max-w-md" title="Start Table">
    <div class="space-y-3 p-4">
        <div class="flex items-center justify-between rounded-md border border-slate-300 bg-slate-50 px-3 py-2.5">
            <span class="text-[11px] font-black uppercase tracking-wide text-slate-500">Table</span>
            <span class="pos-num text-[17px] font-black text-slate-900" x-text="table(startDraft.tableId)?.id"></span>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Guest count</p>
            <div class="flex items-center gap-3">
                <x-pos.qty-control dec="startDraft.guests = Math.max(1, startDraft.guests - 1)" inc="startDraft.guests++" value="startDraft.guests" />
                <span class="pos-num text-[11.5px] font-semibold text-slate-500" x-text="'of ' + (table(startDraft.tableId)?.seats ?? '—') + ' seats'"></span>
            </div>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Waiter</p>
            <div class="grid grid-cols-2 gap-1.5">
                <template x-for="w in waiterNames" :key="w">
                    <button type="button" @click="startDraft.waiter = w"
                            :class="startDraft.waiter === w ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                            class="h-10 rounded-md border text-[12.5px] font-bold" x-text="w"></button>
                </template>
            </div>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Customer</p>
            <input x-model="startDraft.customer" data-autofocus placeholder="+ Add customer (optional)"
                   class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium placeholder:text-slate-400 focus:border-slate-900 focus:outline-none" />
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Order note <span class="font-normal normal-case text-slate-400">(optional)</span></p>
            <input x-model="startDraft.note" placeholder="Anything the kitchen or floor should know…"
                   class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium placeholder:text-slate-400 focus:border-slate-900 focus:outline-none" />
        </div>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmStart()" :disabled="!startDraft.waiter"
                    class="h-10 flex-1 rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-300">Start Order</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
