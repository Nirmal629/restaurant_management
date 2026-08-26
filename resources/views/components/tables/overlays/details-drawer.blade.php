{{-- TableDetailsDrawer — occupied tables and merged groups, section 12 + 34. --}}
<x-pos.dialog name="details" variant="drawer" width="max-w-md" title="Table details" :subtitle="null">
    <template x-if="activeCard">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="pos-num text-[20px] font-black leading-none text-slate-900" x-text="activeCard.label"></span>
                    <span x-show="activeCard.kind === 'group'"
                          class="rounded bg-slate-900 px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-white">Group</span>
                    <x-tables.status-badge expr="activeCard.status" />
                </div>
                <p class="pos-num mt-1 text-[11px] font-semibold text-slate-500" x-text="'Order ' + activeCard.orderCode + ' · ' + floorLabel(activeCard.floor)"></p>

                <template x-if="activeCard.kind === 'group'">
                    <p class="mt-1 text-[11px] font-semibold text-slate-500">
                        Combined capacity <span class="pos-num font-bold text-slate-800" x-text="activeCard.seats"></span> ·
                        Primary table <span class="pos-num font-bold text-slate-800" x-text="primaryOfGroup(activeCard.groupId)?.id"></span>
                    </p>
                </template>
            </div>

            <div class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                <div class="rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Guests</p>
                    <p class="pos-num text-[16px] font-black text-slate-900" x-text="(activeCard.guests || 0) + ' / ' + activeCard.seats"></p>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Duration</p>
                    <p class="pos-num text-[16px] font-black" :class="isLong(activeCard) ? 'text-rose-600' : 'text-slate-900'" x-text="activeCard.since + ' min'"></p>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Waiter</p>
                    <p class="text-[13px] font-bold text-slate-900" x-text="activeCard.waiter"></p>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Running Total</p>
                    <p class="pos-num text-[16px] font-black text-slate-900" x-text="money(activeCard.amount)"></p>
                </div>
                <div class="col-span-2 rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Customer</p>
                    <p class="text-[13px] font-bold text-slate-900" x-text="activeCard.customer"></p>
                </div>
            </div>

            {{-- Kitchen summary --}}
            <div class="border-b border-slate-200 p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Order status: In kitchen</p>
                <div class="grid grid-cols-3 gap-1.5">
                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1.5 text-center">
                        <p class="pos-num text-[15px] font-black text-slate-900" x-text="activeCard.kitchen?.new ?? 0"></p>
                        <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">New</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1.5 text-center">
                        <p class="pos-num text-[15px] font-black text-orange-700" x-text="activeCard.kitchen?.prep ?? 0"></p>
                        <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Preparing</p>
                    </div>
                    <div class="rounded-md border border-emerald-300 bg-emerald-50 px-2 py-1.5 text-center">
                        <p class="pos-num text-[15px] font-black text-emerald-700" x-text="activeCard.kitchen?.ready ?? 0"></p>
                        <p class="text-[9px] font-bold uppercase tracking-wide text-emerald-700">Ready</p>
                    </div>
                </div>
            </div>

            {{-- Current items --}}
            <div class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Current Items</p>
                <div class="divide-y divide-slate-100 rounded-md border border-slate-200 bg-white">
                    <template x-for="(it, i) in activeCard.items || []" :key="i">
                        <div class="flex items-center gap-2 px-3 py-2">
                            <span class="pos-num text-[12px] font-bold text-slate-900" x-text="it.qty + '×'"></span>
                            <span class="min-w-0 flex-1 truncate text-[12.5px] font-semibold text-slate-800" x-text="it.name"></span>
                            <span :class="statusClass(it.state === 'PREPARING' ? 'occupied' : it.state === 'READY' ? 'available' : 'cleaning')"
                                  class="rounded border px-1.5 py-px text-[9px] font-bold uppercase tracking-wide" x-text="it.state"></span>
                        </div>
                    </template>
                    <p x-show="!(activeCard.items || []).length" class="px-3 py-4 text-center text-[11.5px] text-slate-400">No items punched yet.</p>
                </div>
            </div>

            {{-- Unmerge, group only --}}
            <div x-show="activeCard.kind === 'group'" class="px-3 pb-3">
                <button type="button" @click="openUnmerge(activeCard.groupId)"
                        class="flex h-9 w-full items-center justify-center gap-1.5 rounded-md border border-dashed border-slate-300 text-[11.5px] font-bold text-slate-600 hover:border-slate-900 hover:text-slate-900">
                    <x-pos.icon name="split" class="h-3.5 w-3.5" /> Unmerge tables
                </button>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <template x-if="activeCard">
            <div class="space-y-1.5">
                <button type="button" @click="goToPos(activeCard)"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">
                    <x-pos.icon name="cash" class="h-4 w-4" /> Open POS
                </button>
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" @click="addItems(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Add Items</button>
                    <button type="button" @click="viewOrder(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">View Order</button>
                    <button type="button" @click="openChangeWaiter(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Change Waiter</button>
                    <button type="button" @click="openTransfer(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Transfer Table</button>
                    <button type="button" x-show="activeCard.kind !== 'group'" @click="openMerge(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Merge Table</button>
                    <button type="button" @click="printBill(activeCard)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Print Running Bill</button>
                </div>
                <button type="button" @click="generateBill(activeCard)" :disabled="saving"
                        class="flex h-9 w-full items-center justify-center gap-1.5 rounded-md bg-amber-500 text-[11.5px] font-black uppercase tracking-wide text-slate-950 hover:bg-amber-400 disabled:cursor-wait disabled:bg-slate-300">
                    <span x-show="saving && savingAction === 'billing'" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-900/30 border-t-slate-900"></span>
                    <span x-text="saving && savingAction === 'billing' ? 'Generating...' : 'Generate Bill'"></span>
                </button>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>
