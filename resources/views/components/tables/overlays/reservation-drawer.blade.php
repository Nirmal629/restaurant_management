{{-- ReservationDrawer — section 18-19. --}}
<x-pos.dialog name="reservation" variant="drawer" width="max-w-sm" title="Reservation" :subtitle="null">
    <template x-if="activeReservation()">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="pos-num text-[16px] font-black text-slate-900" x-text="activeReservation().id"></span>
                    <span class="rounded border border-violet-400 bg-violet-50 px-1.5 py-px text-[9.5px] font-bold uppercase tracking-wide text-violet-800"
                          x-text="activeReservation().status"></span>
                </div>
                <p class="pos-num mt-0.5 text-[11px] font-semibold text-slate-500" x-text="'Table ' + (activeReservation().tableId || activeReservation().table || activeTableId)"></p>
            </div>

            <div class="space-y-2 p-3">
                <div class="flex items-center gap-2.5 rounded-md border border-slate-200 bg-white p-2.5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-[13px] font-bold text-slate-700"
                          x-text="activeReservation().customer.charAt(0)"></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-bold text-slate-900" x-text="activeReservation().customer"></p>
                        <p class="pos-num flex items-center gap-1 text-[11px] font-medium text-slate-500">
                            <x-pos.icon name="phone" class="h-3 w-3" /> <span x-text="activeReservation().phone"></span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-md border border-slate-200 bg-white p-2.5">
                        <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Date</p>
                        <p class="text-[13px] font-bold text-slate-900" x-text="activeReservation().date"></p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-white p-2.5">
                        <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Time</p>
                        <p class="pos-num text-[13px] font-bold text-slate-900" x-text="activeReservation().time"></p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-white p-2.5">
                        <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Guests</p>
                        <p class="pos-num text-[13px] font-bold text-slate-900" x-text="activeReservation().guests"></p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-white p-2.5">
                        <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Table</p>
                        <p class="pos-num text-[13px] font-bold text-slate-900" x-text="activeReservation().tableId || activeReservation().table || activeTableId"></p>
                    </div>
                </div>

                <div x-show="activeReservation().notes" class="rounded-md border border-amber-200 bg-amber-50 p-2.5">
                    <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-amber-700">Notes</p>
                    <p class="text-[12px] font-medium text-amber-900" x-text="activeReservation().notes"></p>
                </div>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <template x-if="activeReservation()">
            <div class="space-y-1.5">
                <button type="button" @click="activeReservation().synthetic ? notify('Reservation details are not linked to an active reservation record', 'warn') : markArrived(activeReservation().id)"
                        class="h-9 w-full rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Mark Arrived</button>
                <button type="button" @click="activeReservation().synthetic ? openStart(activeCard) : seatFromReservation(activeReservation().id)"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 text-[12.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">
                    <x-pos.icon name="table" class="h-4 w-4" /> Seat Customer
                </button>
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" @click="notify('Reservation editing uses the same form as creating one')" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Edit Reservation</button>
                    <button type="button" @click="activeReservation().synthetic ? notify('Reservation details are not linked to an active reservation record', 'warn') : openChangeReservationTable(activeReservation().id)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Change Table</button>
                </div>
                <button type="button" @click="activeReservation().synthetic ? notify('Reservation details are not linked to an active reservation record', 'warn') : cancelReservation(activeReservation().id)"
                        class="h-9 w-full rounded-md border border-rose-300 bg-white text-[11.5px] font-bold text-rose-600 hover:border-rose-500 hover:bg-rose-50">Cancel Reservation</button>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>
