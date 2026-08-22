{{-- EditTableModal — section 28. Prefers Deactivate over destructive delete. --}}
<x-pos.dialog name="editTable" width="max-w-md" title="Edit Table">
    <div class="space-y-3 p-4">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Table Number</label>
                <input x-model="editTableDraft.number" disabled
                       class="pos-num h-10 w-full rounded-md border border-slate-200 bg-slate-100 px-2.5 text-[13px] font-bold text-slate-500" />
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Name</label>
                <input x-model="editTableDraft.name" data-autofocus placeholder="Optional label"
                       class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Floor</label>
                <select x-model="editTableDraft.floor"
                        class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                    <template x-for="f in floors" :key="f.key">
                        <option :value="f.key" x-text="f.label"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Capacity</label>
                <x-pos.qty-control dec="editTableDraft.capacity = Math.max(1, editTableDraft.capacity - 1)" inc="editTableDraft.capacity++" value="editTableDraft.capacity" />
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Shape</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach (['square' => 'Square', 'rect' => 'Rectangle', 'round' => 'Round'] as $val => $label)
                    <button type="button" @click="editTableDraft.shape = '{{ $val }}'"
                            :class="editTableDraft.shape === '{{ $val }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                            class="h-10 rounded-md border text-[12px] font-bold">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <label class="flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2.5">
            <input type="checkbox" x-model="editTableDraft.active" class="h-4 w-4 accent-slate-900">
            <span class="text-[12.5px] font-bold text-slate-700">Active <span class="font-normal text-slate-400">— uncheck to deactivate instead of deleting</span></span>
        </label>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmEditTable()"
                    class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Save Changes</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
