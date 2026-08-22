{{-- FindTableModal — section 25. --}}
<x-pos.dialog name="find" width="max-w-lg" title="Find Table" subtitle="Suitable available tables are highlighted first.">
    <div class="space-y-3 p-4">
        <div class="flex items-end gap-3">
            <div>
                <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Guests</p>
                <x-pos.qty-control dec="findDraft.guests = Math.max(1, findDraft.guests - 1)" inc="findDraft.guests++" value="findDraft.guests" />
            </div>
            <div class="flex-1">
                <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Floor preference</p>
                <select x-model="findDraft.floor"
                        class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                    <option value="all">Any floor</option>
                    <template x-for="f in floors" :key="f.key">
                        <option :value="f.key" x-text="f.label"></option>
                    </template>
                </select>
            </div>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Recommended</p>
            <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">
                <template x-for="t in recommendedTables" :key="t.id">
                    <button type="button" @click="pickRecommended(t)"
                            class="rounded-md border-2 border-emerald-400 bg-emerald-50 p-2.5 text-left hover:border-emerald-600">
                        <span class="pos-num block text-[16px] font-black text-slate-900" x-text="t.id"></span>
                        <span class="pos-num block text-[10.5px] font-semibold text-slate-500" x-text="t.seats + ' seats · ' + floorLabel(t.floor)"></span>
                        <span class="mt-1 inline-block rounded bg-emerald-600 px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-white">Seat here</span>
                    </button>
                </template>
                <p x-show="!recommendedTables.length" class="col-span-full py-8 text-center text-[12.5px] font-semibold text-slate-400">
                    No available table seats <span x-text="findDraft.guests"></span>+ guests right now — try Merge Table on an occupied floor instead.
                </p>
            </div>
        </div>
    </div>
</x-pos.dialog>
