{{-- PaymentSuccessModal — compact, then straight back into service. --}}
<x-pos.dialog name="success" width="max-w-sm" title="Payment Successful" :subtitle="null">
    <div class="p-4 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-100 text-emerald-600">
            <x-pos.icon name="check" class="h-7 w-7" stroke="3" />
        </span>
        <p class="mt-2 text-[15px] font-black uppercase tracking-wide text-emerald-700">Payment Successful</p>
        <p class="pos-num mt-1 text-[12px] font-semibold text-slate-500" x-text="invoice.code"></p>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Total</p>
                <p class="pos-num text-[17px] font-black text-slate-900" x-text="money(grandTotal)"></p>
            </div>
            <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5">
                <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Paid</p>
                <p class="pos-num text-[17px] font-black text-emerald-700" x-text="money(paidTotal)"></p>
            </div>
        </div>

        <div class="mt-2 space-y-1 rounded-md border border-slate-200 bg-white p-2.5 text-left">
            <template x-for="p in payments" :key="p.at + p.method">
                <div class="flex justify-between text-[11.5px]"><span class="font-semibold text-slate-600" x-text="p.label"></span><span class="pos-num font-bold text-slate-900" x-text="money(p.amount)"></span></div>
            </template>
        </div>
    </div>

    <x-slot:footer>
        <div class="space-y-1.5">
            <div class="grid grid-cols-3 gap-1.5">
                <button type="button" @click="printReceipt()" class="flex h-10 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 bg-white text-[10px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="printer" class="h-4 w-4" /> Print
                </button>
                <button type="button" @click="emailReceipt()" class="flex h-10 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 bg-white text-[10px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="mail" class="h-4 w-4" /> Email
                </button>
                <button type="button" @click="whatsappReceipt()" class="flex h-10 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 bg-white text-[10px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="chat" class="h-4 w-4" /> WhatsApp
                </button>
            </div>
            <button type="button" @click="back(); openCloseTable()" class="h-11 w-full rounded-md bg-slate-900 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Close Table</button>
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" @click="startNewOrder()" class="h-9 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">New Order</button>
                <button type="button" @click="goToPos()" class="h-9 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">Back to POS</button>
            </div>
        </div>
    </x-slot:footer>
</x-pos.dialog>
