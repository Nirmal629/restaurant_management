{{-- KotDetailDrawer — full timeline + secondary actions. Never navigates away from the KDS. --}}
<x-pos.dialog name="detail" variant="drawer" width="max-w-md" title="KOT Detail" :subtitle="null">
    <template x-if="activeTicket">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="pos-num text-[19px] font-black text-slate-900" x-text="'KOT #' + activeTicket.kot"></span>
                    <x-kds.status-badge expr="activeTicket.status" />
                    <span x-show="activeTicket.round > 1" class="rounded bg-indigo-100 px-1.5 py-px text-[9px] font-black uppercase tracking-wide text-indigo-800">Add-On KOT</span>
                </div>
                <p class="pos-num mt-1 text-[11.5px] font-semibold text-slate-500" x-text="activeTicket.orderCode + ' · ' + orderLabel(activeTicket)"></p>
            </div>

            <div class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                <div class="rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Waiter</p>
                    <p class="text-[13px] font-bold text-slate-900" x-text="activeTicket.waiter || '—'"></p>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Guests</p>
                    <p class="pos-num text-[13px] font-bold text-slate-900" x-text="activeTicket.guests || '—'"></p>
                </div>
                <div class="col-span-2 rounded-md border border-slate-200 bg-white p-2.5">
                    <p class="mb-1 text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Timeline</p>
                    <div class="space-y-0.5 text-[11.5px] font-semibold text-slate-600">
                        <p>Ordered: <span class="pos-num font-bold text-slate-900" x-text="new Date(activeTicket.placedAt).toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'})"></span></p>
                        <p x-show="activeTicket.acceptedAt">Accepted: <span class="pos-num font-bold text-slate-900" x-text="new Date(activeTicket.acceptedAt).toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'})"></span></p>
                        <p x-show="activeTicket.startedAt">Started: <span class="pos-num font-bold text-slate-900" x-text="new Date(activeTicket.startedAt).toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'})"></span></p>
                        <p x-show="activeTicket.readyAt">Ready at: <span class="pos-num font-bold text-slate-900" x-text="new Date(activeTicket.readyAt).toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'})"></span></p>
                        <p>Elapsed: <span class="pos-num font-bold" :class="waitLevel(activeTicket) !== 'normal' && 'text-rose-600'" x-text="waitMinutes(activeTicket) + ' min'"></span></p>
                        <p x-show="prepMinutes(activeTicket)">Preparation time: <span class="pos-num font-bold text-slate-900" x-text="prepMinutes(activeTicket) + ' min'"></span></p>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Items</p>
                <div class="space-y-1.5">
                    <template x-for="i in activeTicket.items" :key="i.uid">
                        <div><x-kds.item-row ticket="activeTicket" item="i" /></div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <template x-if="activeTicket">
            <div class="space-y-1.5">
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" @click="openPriority(activeTicket)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Change Priority</button>
                    <button type="button" @click="openReprint(activeTicket)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Reprint KOT</button>
                </div>
                <a href="{{ route('pos') }}" class="flex h-9 w-full items-center justify-center rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">View Full Order</a>
                <template x-if="activeTicket.status === 'new'">
                    <button type="button" @click="acceptTicket(activeTicket)" class="h-11 w-full rounded-md bg-slate-900 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Accept</button>
                </template>
                <template x-if="activeTicket.status === 'accepted'">
                    <button type="button" @click="startPreparing(activeTicket)" class="h-11 w-full rounded-md bg-orange-500 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-orange-400">Start Preparing</button>
                </template>
                <template x-if="activeTicket.status === 'preparing'">
                    <button type="button" @click="markAllReady(activeTicket)" class="h-11 w-full rounded-md bg-emerald-600 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">Mark Ready</button>
                </template>
                <template x-if="activeTicket.status === 'ready'">
                    <button type="button" @click="markPickedUp(activeTicket)" class="h-11 w-full rounded-md bg-slate-900 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Mark Picked Up</button>
                </template>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>
