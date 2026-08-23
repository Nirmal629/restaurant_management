{{-- BillingQueuePanel - active cashier work first; closed bills live in history. --}}
<aside class="bil-queue-col border-r border-slate-200 bg-white">
    <div class="pos-dock border-b border-slate-200 bg-white px-2 py-2">
        <div class="mb-1.5 flex items-center gap-2 px-1">
            <h2 class="text-[11.5px] font-black uppercase tracking-[0.07em] text-slate-800">Billing</h2>
            <span class="flex-1"></span>
            <span class="pos-num rounded bg-slate-100 px-1.5 py-px text-[10px] font-bold text-slate-500" x-text="queueMode === 'active' ? billingOrders.length : billingHistory.length"></span>
        </div>
        <div class="grid grid-cols-2 gap-1 rounded-md bg-slate-100 p-1">
            <button type="button" @click="queueMode = 'active'"
                    :class="queueMode === 'active' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"
                    class="h-7 rounded text-[10.5px] font-black uppercase tracking-wide">Active</button>
            <button type="button" @click="queueMode = 'history'"
                    :class="queueMode === 'history' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"
                    class="h-7 rounded text-[10.5px] font-black uppercase tracking-wide">History</button>
        </div>
    </div>

    <div class="pos-scroll space-y-1.5 p-2">
        <template x-if="queueMode === 'active'">
            <div class="space-y-1.5">
                <template x-for="bill in billingOrders" :key="bill.id">
                    <button type="button" @click="selectOrder(bill.id)"
                            :class="bill.id === order.id ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400'"
                            class="w-full rounded-md border p-2 text-left">
                        <div class="flex items-start gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="pos-num truncate text-[12px] font-black" x-text="bill.code"></p>
                                <p class="mt-0.5 truncate text-[10.5px] font-semibold opacity-70" x-text="bill.table + ' · ' + bill.customer"></p>
                            </div>
                            <span :class="statusClass(bill.status)" class="shrink-0 rounded border px-1.5 py-0.5 text-[8.5px] font-black uppercase tracking-wide" x-text="statusLabel(bill.status)"></span>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-1 text-[9.5px] font-bold">
                            <span class="rounded bg-slate-100 px-1.5 py-1 text-slate-500" :class="bill.id === order.id && 'bg-white/10 text-slate-200'"><span x-text="bill.items"></span> items</span>
                            <span class="pos-num rounded bg-slate-100 px-1.5 py-1 text-slate-500" :class="bill.id === order.id && 'bg-white/10 text-slate-200'" x-text="money(bill.total)"></span>
                            <span class="pos-num rounded bg-emerald-50 px-1.5 py-1 text-emerald-700" :class="bill.id === order.id && 'bg-white/10 text-emerald-200'" x-text="money(bill.due)"></span>
                        </div>
                    </button>
                </template>

                <p x-show="!billingOrders.length" class="py-12 text-center text-[12px] font-semibold text-slate-400">No active bills.</p>
            </div>
        </template>

        <template x-if="queueMode === 'history'">
            <div class="space-y-1.5">
                <template x-for="bill in billingHistory" :key="bill.id">
                    <button type="button" @click="selectOrder(bill.orderId)"
                            :class="bill.orderId === order.id ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400'"
                            class="w-full rounded-md border p-2 text-left">
                        <div class="flex items-start gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="pos-num truncate text-[12px] font-black" x-text="bill.code"></p>
                                <p class="mt-0.5 truncate text-[10.5px] font-semibold opacity-70" x-text="bill.orderCode + ' · ' + bill.customer"></p>
                            </div>
                            <span :class="statusClass(bill.status)" class="shrink-0 rounded border px-1.5 py-0.5 text-[8.5px] font-black uppercase tracking-wide" x-text="statusLabel(bill.status)"></span>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-1 text-[9.5px] font-bold">
                            <span class="truncate rounded bg-slate-100 px-1.5 py-1 text-slate-500" :class="bill.orderId === order.id && 'bg-white/10 text-slate-200'" x-text="bill.at || '-'"></span>
                            <span class="pos-num rounded bg-slate-100 px-1.5 py-1 text-slate-500" :class="bill.orderId === order.id && 'bg-white/10 text-slate-200'" x-text="money(bill.total)"></span>
                            <span class="pos-num rounded bg-rose-50 px-1.5 py-1 text-rose-700" :class="bill.orderId === order.id && 'bg-white/10 text-rose-200'" x-text="money(bill.refunded)"></span>
                        </div>
                    </button>
                </template>

                <p x-show="!billingHistory.length" class="py-12 text-center text-[12px] font-semibold text-slate-400">No invoice history yet.</p>
            </div>
        </template>
    </div>
</aside>
