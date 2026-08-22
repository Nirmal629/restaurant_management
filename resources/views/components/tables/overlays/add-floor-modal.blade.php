{{-- AddFloorModal — section 29. --}}
<x-pos.dialog name="addFloor" width="max-w-sm" title="Add Floor">
    <div class="space-y-3 p-4">
        <div>
            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Floor Name</label>
            <input x-model="addFloorDraft.name" data-autofocus placeholder="e.g. Rooftop"
                   class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[13px] font-bold focus:border-slate-900 focus:outline-none" />
        </div>
        <div>
            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Description</label>
            <input x-model="addFloorDraft.description" placeholder="Open-air seating section"
                   class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Display Order</label>
                <input x-model="addFloorDraft.order" type="number" min="1"
                       class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
            <label class="flex items-center gap-2 self-end rounded-md border border-slate-200 bg-slate-50 px-3 py-2.5">
                <input type="checkbox" x-model="addFloorDraft.active" class="h-4 w-4 accent-slate-900">
                <span class="text-[12px] font-bold text-slate-700">Active</span>
            </label>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmAddFloor()" :disabled="!addFloorDraft.name.trim()"
                    class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Add Floor</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
