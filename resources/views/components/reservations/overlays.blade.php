{{-- ReservationDetailDrawer — full detail + activity history. --}}
<x-pos.dialog name="detail" variant="drawer" width="max-w-md" title="Reservation" :subtitle="null">
    <template x-if="activeReservation">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="pos-num text-[16px] font-black text-slate-900" x-text="activeReservation.id"></span>
                    <x-admin.badge expr="activeReservation.status" />
                </div>
                <p class="mt-1 text-[13px] font-bold text-slate-800" x-text="activeReservation.customer"></p>
                <p class="pos-num text-[11px] font-semibold text-slate-500" x-text="activeReservation.phone + (activeReservation.email ? ' · ' + activeReservation.email : '')"></p>
            </div>

            <div class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Date</p><p class="pos-num text-[13px] font-bold text-slate-900" x-text="prettyDate(activeReservation.date)"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Time</p><p class="pos-num text-[13px] font-bold text-slate-900" x-text="timeLabel(activeReservation.time)"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Guests</p><p class="pos-num text-[13px] font-bold text-slate-900" x-text="activeReservation.guests"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Table</p><p class="pos-num text-[13px] font-bold text-slate-900" x-text="activeReservation.table || 'Not assigned'"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Floor</p><p class="text-[12px] font-bold text-slate-900" x-text="floorLabel(activeReservation.floor)"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Source</p><p class="text-[12px] font-bold text-slate-900" x-text="activeReservation.source"></p></div>
                <div x-show="activeReservation.occasion !== 'None'" class="col-span-2 rounded-md border border-violet-200 bg-violet-50 p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-violet-700">Occasion</p><p class="text-[12px] font-bold text-violet-900" x-text="activeReservation.occasion"></p></div>
                <div x-show="activeReservation.request" class="col-span-2 rounded-md border border-amber-200 bg-amber-50 p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-amber-700">Special Request</p><p class="text-[12px] font-medium text-amber-900" x-text="activeReservation.request"></p></div>
                <div x-show="activeReservation.deposit" class="col-span-2 rounded-md border border-emerald-200 bg-emerald-50 p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-emerald-700">Deposit</p><p class="pos-num text-[13px] font-bold text-emerald-800" x-text="money(activeReservation.deposit)"></p></div>
            </div>

            <div class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Activity History</p>
                <div class="space-y-2 border-l-2 border-slate-200 pl-3">
                    <template x-for="(h, i) in activeReservation.history" :key="i">
                        <div>
                            <p class="text-[11.5px] font-semibold text-slate-700" x-text="h.text"></p>
                            <p class="text-[10px] font-medium text-slate-400" x-text="h.at"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
    <x-slot:footer>
        <template x-if="activeReservation">
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" x-show="activeReservation.status === 'pending'" @click="confirmReservation(activeReservation)" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="col-span-2 h-10 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-wait disabled:bg-slate-300">Confirm</button>
                <button type="button" x-show="activeReservation.status === 'confirmed'" @click="markArrived(activeReservation)" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="col-span-2 h-10 rounded-md bg-amber-500 text-[12px] font-black uppercase tracking-wide text-slate-950 hover:bg-amber-400 disabled:cursor-wait disabled:bg-slate-300 disabled:text-white">Mark Arrived</button>
                <button type="button" x-show="activeReservation.status === 'arrived'" @click="openSeat(activeReservation)" class="col-span-2 h-10 rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">Seat Customer</button>
                <a href="{{ route('pos') }}" x-show="activeReservation.status === 'seated'" class="col-span-2 flex h-10 items-center justify-center rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">Open POS</a>
                <button type="button" x-show="!['seated','completed','cancelled','no_show'].includes(activeReservation.status)" @click="openEdit(activeReservation)" class="h-9 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">Edit</button>
                <button type="button" x-show="!['seated','completed','cancelled','no_show'].includes(activeReservation.status)" @click="openChangeTable(activeReservation)" class="h-9 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">Change Table</button>
                <button type="button" x-show="!['seated','completed','cancelled','no_show'].includes(activeReservation.status)" @click="openCancel(activeReservation)" class="col-span-2 h-9 rounded-md border border-rose-300 bg-white text-[11px] font-bold text-rose-600 hover:border-rose-500 hover:bg-rose-50">Cancel Reservation</button>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>


{{-- Create / Edit Reservation --}}
<x-pos.dialog name="create" width="max-w-lg" title="New Reservation">
    <div class="grid grid-cols-2 gap-3 p-4">
        <div class="col-span-2 grid grid-cols-2 gap-2">
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Customer Name</label><input x-model="createDraft.customer" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Mobile</label><input x-model="createDraft.phone" inputmode="tel" maxlength="10" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        </div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Email <span class="font-normal normal-case text-slate-400">(optional)</span></label><input x-model="createDraft.email" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>

        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Date</label><input x-model="createDraft.date" type="date" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Time</label><input x-model="createDraft.time" type="time" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>

        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Guests</label><x-pos.qty-control dec="createDraft.guests = Math.max(1, createDraft.guests - 1)" inc="createDraft.guests++" value="createDraft.guests" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Preferred Floor</label>
            <select x-model="createDraft.floor" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                <template x-for="f in floors" :key="f.key"><option :value="f.key" x-text="f.label"></option></template>
            </select>
        </div>

        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Preferred Table <span class="font-normal normal-case text-slate-400">(optional)</span></label>
            <div class="flex gap-1.5">
                <select x-model="createDraft.table" class="h-10 flex-1 rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                    <option :value="null">No preference</option>
                    <template x-for="t in preferredTableOptions" :key="t.id">
                        <option :value="t.reserveCode || t.id" :disabled="!tableAvailableForDraft(t, createDraft)" x-text="tableOptionLabel(t)"></option>
                    </template>
                </select>
                <button type="button" @click="openDraftFinder()" class="h-10 shrink-0 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-bold text-slate-700 hover:border-slate-900">Find Table</button>
            </div>
            <p x-show="createDraft.table" class="mt-1 text-[10.5px] font-semibold"
               :class="tableAvailableForDraft(table(createDraft.table), createDraft) ? 'text-emerald-700' : 'text-rose-600'"
               x-text="tableAvailability(table(createDraft.table), createDraft).label"></p>
        </div>

        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Occasion</label>
            <select x-model="createDraft.occasion" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                <template x-for="o in occasions" :key="o"><option x-text="o"></option></template>
            </select>
        </div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Source</label>
            <select x-model="createDraft.source" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                <template x-for="s in sources" :key="s"><option x-text="s"></option></template>
            </select>
        </div>

        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Special Request</label><textarea x-model="createDraft.request" rows="2" placeholder="Window seating, allergy notes, cake arrangement…" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveReservation()" :disabled="saving || !canSaveDraft()" :aria-busy="saving ? 'true' : 'false'"
                    class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-text="createDraft.id ? 'Save Changes' : 'Create Reservation'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Find Available Table --}}
<x-pos.dialog name="find" width="max-w-lg" title="Find Available Table">
    <div class="space-y-3 p-4">
        <div class="grid grid-cols-3 gap-2">
            <div><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-600">Guests</label><x-pos.qty-control dec="findDraft.guests = Math.max(1, findDraft.guests - 1)" inc="findDraft.guests++" value="findDraft.guests" /></div>
            <div><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-600">Date</label><input x-model="findDraft.date" type="date" class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <div><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-600">Time</label><input x-model="findDraft.time" type="time" class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <button type="button" @click="findDraft.floor = 'all'" :class="findDraft.floor === 'all' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold">Any floor</button>
            <template x-for="f in floors" :key="f.key">
                <button type="button" @click="findDraft.floor = f.key" :class="findDraft.floor === f.key ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold" x-text="f.label"></button>
            </template>
        </div>
        <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">
            <template x-for="t in findResults" :key="t.id">
                <button type="button" @click="pickFoundTable(t)" :disabled="!tableAvailableForDraft(t, findDraft)"
                        :class="availabilityClass(t, findDraft)"
                        class="rounded-md border-2 p-2.5 text-left disabled:cursor-not-allowed">
                    <span class="pos-num block text-[15px] font-black text-slate-900" x-text="t.id"></span>
                    <span class="pos-num block text-[10.5px] font-semibold text-slate-500" x-text="t.seats + ' seats · ' + floorLabel(t.floor)"></span>
                    <span class="mt-1 block text-[10px] font-black uppercase tracking-wide text-slate-600" x-text="tableStatusLabel(t.status)"></span>
                    <span class="mt-0.5 block text-[10px] font-semibold text-slate-500" x-text="tableAvailability(t, findDraft).label"></span>
                </button>
            </template>
            <p x-show="!findResults.length" class="col-span-full py-8 text-center text-[12.5px] font-semibold text-slate-400">No tables match this party size.</p>
        </div>
    </div>
    <x-slot:footer>
        <button type="button" @click="swap('create')" class="h-10 w-full rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Back to Reservation</button>
    </x-slot:footer>
</x-pos.dialog>


{{-- Seat / Change Table --}}
<x-pos.dialog name="seat" width="max-w-md" title="Seat Customer">
    <div class="p-4">
        <p class="mb-2 text-[11.5px] font-semibold text-slate-500">Assign a table for <span class="font-bold text-slate-900" x-text="reservationCustomer(seatDraft.id)"></span>:</p>
        <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));">
            <template x-for="t in tables" :key="t.id">
                <button type="button" @click="seatDraft.table = t.id"
                        :class="seatDraft.table === t.id ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                        class="rounded-md border-2 py-2">
                    <span class="pos-num block text-[14px] font-black" x-text="t.id"></span>
                    <span class="pos-num block text-[10px] font-semibold opacity-70" x-text="t.seats + ' seats'"></span>
                </button>
            </template>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmSeat()" :disabled="saving || !seatDraft.table" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-emerald-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-300">Confirm</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Cancel Reservation --}}
<x-pos.dialog name="cancel" width="max-w-sm" title="Cancel Reservation">
    <div class="space-y-3 p-4">
        <p class="text-[13px] font-semibold text-slate-700">Cancel reservation for <span class="font-bold text-slate-900" x-text="reservationCustomer(cancelDraft.id)"></span>?</p>
        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Reason <span class="text-rose-600">*</span></p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="r in cancelReasons" :key="r">
                    <button type="button" @click="cancelDraft.reason = r" :class="cancelDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11.5px] font-bold" x-text="r"></button>
                </template>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Keep Reservation</button>
            <button type="button" @click="confirmCancel()" :disabled="saving || !cancelDraft.reason" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Cancel Reservation</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
