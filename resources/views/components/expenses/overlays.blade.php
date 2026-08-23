{{-- Expense Detail Drawer --}}
<x-pos.dialog name="detail" variant="drawer" width="max-w-md" title="Expense Detail" :subtitle="null">
    <template x-if="activeExpense">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2"><span class="pos-num text-[16px] font-black text-slate-900" x-text="activeExpense.id"></span><x-admin.badge expr="activeExpense.status" /></div>
                <p class="text-[12.5px] font-bold text-slate-800" x-text="activeExpense.description"></p>
            </div>
            <div class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Category</p><p class="text-[12.5px] font-bold text-slate-900" x-text="activeExpense.category"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Amount</p><p class="pos-num text-[14px] font-black text-slate-900" x-text="money(activeExpense.amount)"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Payment Method</p><p class="text-[12.5px] font-bold text-slate-900" x-text="activeExpense.method"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Date</p><p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="activeExpense.date"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5" x-show="activeExpense.vendor"><p class="text-[9.5px] font-black uppercase text-slate-400">Vendor</p><p class="text-[12.5px] font-bold text-slate-900" x-text="activeExpense.vendor"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Recorded By</p><p class="text-[12.5px] font-bold text-slate-900" x-text="activeExpense.employee"></p></div>
                <div class="col-span-2 rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Receipt</p><p class="text-[12px] font-semibold" :class="activeExpense.receipt ? 'text-emerald-700' : 'text-slate-400'" x-text="activeExpense.receipt ? 'Attached (placeholder)' : 'Not attached'"></p></div>
            </div>
            <div class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Timeline</p>
                <div class="space-y-2 border-l-2 border-slate-200 pl-3">
                    <template x-for="(h, i) in (timeline[activeExpense.id] || [])" :key="i">
                        <div><p class="text-[11.5px] font-semibold text-slate-700" x-text="h.text"></p><p class="text-[10px] font-medium text-slate-400" x-text="h.at"></p></div>
                    </template>
                    <p x-show="!(timeline[activeExpense.id] || []).length" class="text-[11px] text-slate-400">No activity recorded.</p>
                </div>
            </div>
        </div>
    </template>
    <x-slot:footer>
        <template x-if="activeExpense">
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" x-show="activeExpense.status === 'draft'" @click="approve(activeExpense)" class="h-9 rounded-md bg-sky-600 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-sky-500">Approve</button>
                <button type="button" x-show="activeExpense.status === 'draft'" @click="openReject(activeExpense)" class="h-9 rounded-md border border-rose-300 bg-white text-[11.5px] font-bold text-rose-600 hover:bg-rose-50">Reject</button>
                <button type="button" x-show="activeExpense.status === 'approved'" @click="markPaid(activeExpense)" class="col-span-2 h-9 rounded-md bg-emerald-600 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-emerald-500">Mark Paid</button>
                <button type="button" @click="openEdit(activeExpense)" class="col-span-2 h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Edit</button>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>


{{-- Create / Edit Expense --}}
<x-pos.dialog name="form" width="max-w-lg" title="Record Expense">
    <div class="grid grid-cols-2 gap-3 p-4">
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Date</label><input x-model="draft.date" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" placeholder="DD/MM/YYYY" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Category</label><select x-model="draft.category" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="c in categories" :key="c"><option x-text="c"></option></template></select></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Amount</label><input x-model="draft.amount" data-autofocus type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-bold focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Payment Method</label><select x-model="draft.method" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="m in methods" :key="m"><option x-text="m"></option></template></select></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Vendor <span class="font-normal normal-case text-slate-400">(optional)</span></label><input x-model="draft.vendor" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Description</label><textarea x-model="draft.description" rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Reference</label><input x-model="draft.reference" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div class="flex items-end"><label class="flex h-10 items-center gap-1.5 text-[12px] font-semibold text-slate-700"><input type="checkbox" x-model="draft.receipt" class="h-4 w-4 accent-slate-900"> Receipt attached (placeholder)</label></div>

        <div x-show="needsApproval" class="col-span-2 flex items-start gap-2 rounded-md border border-amber-300 bg-amber-50 p-2.5">
            <x-pos.icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-700" />
            <p class="text-[11px] font-semibold leading-snug text-amber-900">This exceeds the <span x-text="money(threshold)"></span> approval threshold — it will be saved as Draft pending manager approval.</p>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveExpense()" :disabled="!draft.category || !Number(draft.amount) || !draft.description?.trim()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-text="draft.id ? 'Save Changes' : 'Record Expense'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Reject --}}
<x-pos.dialog name="reject" width="max-w-sm" title="Reject Expense">
    <div class="space-y-3 p-4">
        <p class="text-[13px] font-semibold text-slate-700">Reject <span class="pos-num font-bold text-slate-900" x-text="rejectDraft.id"></span>?</p>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Reason</label><textarea x-model="rejectDraft.reason" data-autofocus rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmReject()" :disabled="!rejectDraft.reason?.trim()" class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Reject</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
