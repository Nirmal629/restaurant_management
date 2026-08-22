{{-- TableCloseModal — the closing step of the flow: Payment → Close Order → table freed. --}}
<x-pos.dialog name="closeTable" width="max-w-sm" title="Close Table" :subtitle="null">
    <div class="p-4 text-center">
        <p class="text-[14px] font-bold text-slate-900">Close Table <span class="pos-num" x-text="order.table"></span>?</p>
        <div class="mt-3 grid grid-cols-2 gap-2 text-left">
            <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Order</p>
                <p class="pos-num text-[13px] font-black text-slate-900" x-text="order.code"></p>
            </div>
            <div class="rounded-md border border-emerald-200 bg-emerald-50 p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-emerald-700">Paid</p>
                <p class="pos-num text-[13px] font-black text-emerald-800" x-text="money(paidTotal)"></p>
            </div>
        </div>
        <p class="mt-3 text-[11px] text-slate-500">The table moves to <span class="font-bold text-slate-700">Cleaning</span> per your floor settings.</p>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Not Yet</button>
            <button type="button" @click="confirmCloseTable()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Close Table</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
