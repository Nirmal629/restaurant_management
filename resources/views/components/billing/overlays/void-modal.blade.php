{{-- VoidBillModal — restricted, always manager-approval gated. Never deletes the historical invoice. --}}
<x-pos.dialog name="void" width="max-w-sm" title="Void Bill">
    <div class="space-y-3 p-4">
        <div class="flex items-start gap-2.5 rounded-md border border-rose-300 bg-rose-50 p-3">
            <x-pos.icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-rose-700" />
            <p class="text-[11.5px] font-semibold leading-snug text-rose-900">
                This does not delete <span class="pos-num font-bold" x-text="invoice.code"></span>. It creates a
                reversal/void record and requires manager authorization.
            </p>
        </div>

        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason <span class="text-rose-600">*</span></p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="r in voidReasons" :key="r">
                    <button type="button" @click="voidDraft.reason = r" :class="voidDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'"
                            class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                </template>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmVoid()" :disabled="saving || !voidDraft.reason" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Request Approval</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
