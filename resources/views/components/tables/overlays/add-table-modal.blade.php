{{-- AddTableModal — section 27. --}}
<x-pos.dialog name="addTable" width="max-w-md" title="Add Table">
    <div class="space-y-3 p-4">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Table Number</label>
                <input x-model="addTableDraft.number" data-autofocus placeholder="T21"
                       class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[13px] font-bold focus:border-slate-900 focus:outline-none" />
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Name <span class="font-normal normal-case text-slate-400">(optional)</span></label>
                <input x-model="addTableDraft.name" placeholder="Window Corner"
                       class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Floor</label>
                <select x-model="addTableDraft.floor"
                        class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                    <template x-for="f in floors" :key="f.key">
                        <option :value="f.key" x-text="f.label"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Capacity</label>
                <x-pos.qty-control dec="addTableDraft.capacity = Math.max(1, addTableDraft.capacity - 1)" inc="addTableDraft.capacity++" value="addTableDraft.capacity" />
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Shape</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach (['square' => 'Square', 'rect' => 'Rectangle', 'round' => 'Round'] as $val => $label)
                    <button type="button" @click="addTableDraft.shape = '{{ $val }}'"
                            :class="addTableDraft.shape === '{{ $val }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                            class="h-10 rounded-md border text-[12px] font-bold">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <p class="rounded border border-slate-200 bg-slate-50 p-2 text-[11px] leading-snug text-slate-500">
            Position on the floor plan is set later via <span class="font-bold">Edit Layout</span> — new tables are added to the end of the map.
        </p>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmAddTable()" :disabled="saving || !addTableDraft.number.trim()" :aria-busy="saving ? 'true' : 'false'"
                    class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Add Table</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
