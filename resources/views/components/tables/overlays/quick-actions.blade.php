{{-- Tap on an AVAILABLE table — primary flow, section 10. --}}
<x-pos.dialog name="quick" width="max-w-sm" title="Table" :subtitle="null">
    <template x-if="activeCard">
        <div>
            <div class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3">
                <span class="pos-num grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-emerald-100 text-[16px] font-black text-emerald-800" x-text="activeCard.label"></span>
                <div>
                    <p class="pos-num text-[12.5px] font-bold text-slate-900" x-text="activeCard.seats + ' Seats'"></p>
                    <p class="text-[10.5px] font-semibold text-slate-500" x-text="floorLabel(activeCard.floor)"></p>
                </div>
            </div>

            <div class="space-y-1.5 p-3">
                <button type="button" @click="back(); openStart(activeCard)"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-md bg-slate-900 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
                    <x-pos.icon name="table" class="h-4 w-4" /> Start Dine-In Order
                </button>
                <button type="button" @click="back(); openReserve(activeCard)"
                        class="flex h-10 w-full items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="calendar" class="h-4 w-4" /> Create Reservation
                </button>
                <button type="button" @click="notify('Pick the reservation from Find Table or the floor map to seat it')"
                        class="flex h-10 w-full items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="users" class="h-4 w-4" /> Seat Reservation
                </button>
                <button type="button" @click="markDisabled(activeCard)"
                        class="flex h-10 w-full items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-slate-700 hover:border-rose-500 hover:text-rose-600">
                    <x-pos.icon name="ban" class="h-4 w-4" /> Mark Disabled
                </button>
                <button type="button" @click="back(); openQr(activeCard)"
                        class="flex h-10 w-full items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="qr" class="h-4 w-4" /> View Table Details / QR
                </button>
            </div>
        </div>
    </template>
</x-pos.dialog>
