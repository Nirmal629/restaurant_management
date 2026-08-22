{{-- Split / Unmerge — section 16. Visual architecture only, per the brief. --}}
<x-pos.dialog name="unmerge" width="max-w-md" title="Unmerge Tables">
    <template x-if="activeGroupId && primaryOfGroup(activeGroupId)">
        <div class="space-y-3 p-4">
            <div class="rounded-md border border-slate-300 bg-slate-50 p-3 text-center">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Current Group</p>
                <p class="pos-num text-[19px] font-black text-slate-900" x-text="groupMembers(activeGroupId).map(m => m.id).join(' + ')"></p>
            </div>

            <div class="space-y-1.5">
                <button type="button" @click="confirmUnmerge('keep')"
                        class="flex w-full items-start gap-2.5 rounded-md border border-slate-300 bg-white p-3 text-left hover:border-slate-900">
                    <x-pos.icon name="table" class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" />
                    <span>
                        <span class="block text-[12.5px] font-bold text-slate-900" x-text="'Keep order on ' + primaryOfGroup(activeGroupId).id"></span>
                        <span class="block text-[11px] text-slate-500">Other tables in the group become available again; the running order stays with the primary table.</span>
                    </span>
                </button>

                <button type="button" @click="confirmUnmerge('move')"
                        class="flex w-full items-start gap-2.5 rounded-md border border-slate-300 bg-white p-3 text-left hover:border-slate-900">
                    <x-pos.icon name="swap" class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" />
                    <span>
                        <span class="block text-[12.5px] font-bold text-slate-900">Move selected guests / items to the other table</span>
                        <span class="block text-[11px] text-slate-500">Splits guests evenly for this preview — item-level reassignment happens in POS once billing is wired up.</span>
                    </span>
                </button>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <button type="button" @click="back()"
                class="h-10 w-full rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
    </x-slot:footer>
</x-pos.dialog>
