{{-- ComplimentaryModal — reason + authorized-by required; always manager-approval gated. --}}
<x-pos.dialog name="comp" width="max-w-sm" title="Mark Complimentary">
    <template x-if="items.find(i => i.uid === compDraft.uid)">
        <div class="space-y-3 p-4">
            <div class="flex items-center gap-2 rounded-md border border-slate-300 bg-slate-50 p-2.5">
                <span class="pos-num shrink-0 rounded bg-slate-900 px-1.5 py-px text-[11px] font-bold text-white" x-text="items.find(i => i.uid === compDraft.uid).qty + '×'"></span>
                <span class="min-w-0 flex-1 truncate text-[13px] font-bold text-slate-900" x-text="items.find(i => i.uid === compDraft.uid).name"></span>
                <span class="pos-num text-[13px] font-bold text-slate-900" x-text="money(items.find(i => i.uid === compDraft.uid).amount)"></span>
            </div>

            <div>
                <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason <span class="text-rose-600">*</span></p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="r in compReasons" :key="r">
                        <button type="button" @click="compDraft.reason = r"
                                :class="compDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                                class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                    </template>
                </div>
            </div>

            <textarea x-model="compDraft.note" rows="2" placeholder="Additional note (optional)"
                      class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea>

            <p class="rounded border border-violet-200 bg-violet-50 p-2 text-[11px] leading-snug text-violet-800">
                This always requires manager authorization and is recorded against
                <span class="font-bold" x-text="operator.role + ' ' + operator.name"></span>.
            </p>
        </div>
    </template>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmComplimentary()" :disabled="!compDraft.reason"
                    class="h-10 flex-1 rounded-md bg-violet-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-violet-500 disabled:cursor-not-allowed disabled:bg-slate-300">Request Approval</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
