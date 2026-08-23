{{-- Create Reservation, opened from an AVAILABLE table's quick actions. --}}
<x-pos.dialog name="reserve" width="max-w-md" title="Create Reservation">
    <div class="space-y-3 p-4">
        <div class="flex items-center justify-between rounded-md border border-slate-300 bg-slate-50 px-3 py-2.5">
            <span class="text-[11px] font-black uppercase tracking-wide text-slate-500">Table</span>
            <span class="pos-num text-[17px] font-black text-slate-900" x-text="table(reserveDraft.tableId)?.id"></span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Guest name</label>
                <input x-model="reserveDraft.customer" data-autofocus placeholder="Guest name"
                       class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Phone</label>
                <input x-model="reserveDraft.phone" inputmode="tel" placeholder="Mobile number"
                       class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Time</label>
                <input x-model="reserveDraft.time" placeholder="e.g. 8:00 PM"
                       class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Guests</label>
                <x-pos.qty-control dec="reserveDraft.guests = Math.max(1, reserveDraft.guests - 1)" inc="reserveDraft.guests++" value="reserveDraft.guests" />
            </div>
        </div>

        <div>
            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Notes</label>
            <input x-model="reserveDraft.notes" placeholder="Birthday, window seat, allergies…"
                   class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
        </div>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()"
                    class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmReserve()" :disabled="saving || !reserveDraft.customer.trim() || !reserveDraft.time.trim()" :aria-busy="saving ? 'true' : 'false'"
                    class="h-10 flex-1 rounded-md bg-violet-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-violet-500 disabled:cursor-not-allowed disabled:bg-slate-300">Create Reservation</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
