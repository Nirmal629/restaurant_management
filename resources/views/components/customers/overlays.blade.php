{{-- Customer Profile Drawer — Overview / Orders / Reservations / Loyalty / Notes tabs. --}}
<x-pos.dialog name="profile" variant="drawer" width="max-w-lg" title="Customer Profile" :subtitle="null">
    <template x-if="activeCustomer">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-3">
                    <x-admin.avatar initials-expr="initials(activeCustomer.name)" size="lg" />
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-1.5 truncate text-[14px] font-black text-slate-900">
                            <span x-text="activeCustomer.name"></span>
                            <span x-show="activeCustomer.vip" class="rounded bg-amber-100 px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-amber-800">VIP</span>
                        </p>
                        <p class="pos-num text-[11px] font-semibold text-slate-500" x-text="activeCustomer.phone + (activeCustomer.email ? ' · ' + activeCustomer.email : '')"></p>
                    </div>
                    <button type="button" @click="toggleVip(activeCustomer)" class="h-8 shrink-0 rounded-md border border-slate-300 bg-white px-2.5 text-[10.5px] font-bold text-slate-700 hover:border-slate-900" x-text="activeCustomer.vip ? 'Unmark VIP' : 'Mark VIP'"></button>
                </div>
                <div class="mt-2 flex flex-wrap gap-1">
                    <template x-for="t in activeCustomer.tags" :key="t"><span class="rounded bg-slate-100 px-1.5 py-px text-[9.5px] font-bold uppercase tracking-wide text-slate-600" x-text="t"></span></template>
                </div>
            </div>

            <div class="flex items-center gap-1 border-b border-slate-200 bg-white px-3 py-2 overflow-x-auto">
                @foreach (['overview' => 'Overview', 'orders' => 'Orders', 'reservations' => 'Reservations', 'loyalty' => 'Loyalty', 'notes' => 'Notes'] as $k => $l)
                    <button type="button" @click="activeTab = '{{ $k }}'" :class="activeTab === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="shrink-0 rounded-md border px-2.5 py-1.5 text-[11px] font-bold uppercase tracking-wide">{{ $l }}</button>
                @endforeach
            </div>

            {{-- OVERVIEW --}}
            <div x-show="activeTab === 'overview'" class="p-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Visits</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="activeCustomer.visits"></p></div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Total Spend</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="money(activeCustomer.spend)"></p></div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Average Bill</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="money(activeCustomer.avgBill)"></p></div>
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 p-2.5"><p class="text-[9.5px] font-black uppercase tracking-wide text-emerald-700">Loyalty</p><p class="pos-num text-[16px] font-black text-emerald-800" x-text="activeCustomer.points + ' pts'"></p></div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-[11.5px]">
                    <p class="text-slate-500">Last Visit <span class="pos-num block font-bold text-slate-800" x-text="activeCustomer.lastVisit === '—' ? '—' : formatDate(activeCustomer.lastVisit)"></span></p>
                    <p class="text-slate-500">Customer Since <span class="pos-num block font-bold text-slate-800" x-text="formatDate(activeCustomer.joinedDate)"></span></p>
                    <p x-show="activeCustomer.birthday" class="text-slate-500">Birthday <span class="pos-num block font-bold text-slate-800" x-text="formatDate(activeCustomer.birthday)"></span></p>
                    <p x-show="activeCustomer.anniversary" class="text-slate-500">Anniversary <span class="pos-num block font-bold text-slate-800" x-text="formatDate(activeCustomer.anniversary)"></span></p>
                    <p x-show="activeCustomer.gstin" class="col-span-2 text-slate-500">GSTIN <span class="pos-num block font-bold text-slate-800" x-text="activeCustomer.gstin"></span></p>
                    <p x-show="activeCustomer.allergies" class="col-span-2 text-slate-500">Allergies / Food Notes <span class="block font-bold text-rose-700" x-text="activeCustomer.allergies"></span></p>
                </div>

                <p class="mt-3 mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Favourite Items</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="i in activeCustomer.favoriteItems" :key="i"><span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700" x-text="i"></span></template>
                    <p x-show="!activeCustomer.favoriteItems.length" class="text-[11px] text-slate-400">No order history yet.</p>
                </div>
            </div>

            {{-- ORDERS --}}
            <div x-show="activeTab === 'orders'" class="p-3">
                <div class="space-y-1.5">
                    <template x-for="o in activeCustomer.recentOrders" :key="o.code">
                        <div class="flex items-center justify-between rounded-md border border-slate-200 bg-white p-2.5">
                            <div><p class="pos-num text-[12px] font-bold text-slate-900" x-text="o.code"></p><p class="text-[10.5px] font-semibold text-slate-400" x-text="formatDate(o.date)"></p></div>
                            <span class="pos-num text-[13px] font-black text-slate-900" x-text="money(o.amount)"></span>
                        </div>
                    </template>
                </div>
                <x-admin.empty-state icon="receipt" title="No orders yet" x-show="!activeCustomer.recentOrders.length" />
            </div>

            {{-- RESERVATIONS --}}
            <div x-show="activeTab === 'reservations'" class="p-3">
                <div class="rounded-md border border-slate-200 bg-slate-50 p-3 text-center">
                    <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400">Total Reservations</p>
                    <p class="pos-num text-[20px] font-black text-slate-900" x-text="activeCustomer.reservationsCount"></p>
                </div>
                <a href="{{ route('reservations') }}" class="mt-2 flex h-9 w-full items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="calendar" class="h-3.5 w-3.5" /> View in Reservations
                </a>
            </div>

            {{-- LOYALTY --}}
            <div x-show="activeTab === 'loyalty'" class="p-3">
                <div class="mb-3 flex items-center justify-between rounded-md border border-emerald-200 bg-emerald-50 p-3">
                    <div><p class="text-[9.5px] font-black uppercase tracking-wide text-emerald-700">Balance</p><p class="pos-num text-[20px] font-black text-emerald-800" x-text="activeCustomer.points + ' pts'"></p></div>
                    <button type="button" @click="openLoyalty(activeCustomer)" class="h-9 rounded-md bg-slate-900 px-3 text-[11px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Adjust Points</button>
                </div>
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">History</p>
                <div class="space-y-1.5">
                    <template x-for="(l, i) in (discountLog[activeCustomer.id] || [])" :key="i">
                        <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[11.5px] font-semibold text-slate-700" x-text="l.text"></p><p class="text-[10px] text-slate-400" x-text="l.at"></p></div>
                    </template>
                    <p x-show="!(discountLog[activeCustomer.id] || []).length" class="text-[11px] text-slate-400">No loyalty activity recorded.</p>
                </div>
            </div>

            {{-- NOTES --}}
            <div x-show="activeTab === 'notes'" class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Notes</p>
                <p class="min-h-[60px] rounded-md border border-slate-200 bg-slate-50 p-2.5 text-[12px] text-slate-700" x-text="activeCustomer.notes || 'No notes yet.'"></p>
                <button type="button" @click="openNote(activeCustomer)" class="mt-2 h-9 w-full rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Add / Edit Note</button>
            </div>
        </div>
    </template>
    <x-slot:footer>
        <div class="grid grid-cols-2 gap-1.5">
            <button type="button" @click="openEdit(activeCustomer)" class="h-9 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Edit Customer</button>
            <a href="{{ route('reservations') }}" class="flex h-9 items-center justify-center rounded-md bg-slate-900 text-[11.5px] font-bold text-white hover:bg-slate-800">Create Reservation</a>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Add / Edit Customer --}}
<x-pos.dialog name="form" width="max-w-lg" title="Customer Details">
    <div class="grid grid-cols-2 gap-3 p-4">
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Name</label><input x-model="draft.name" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Mobile</label><input x-model="draft.phone" inputmode="tel" maxlength="10" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Email</label><input x-model="draft.email" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Birthday</label><input x-model="draft.birthday" type="date" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Anniversary</label><input x-model="draft.anniversary" type="date" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Address <span class="font-normal normal-case text-slate-400">(optional)</span></label><textarea x-model="draft.address" rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">GSTIN <span class="font-normal normal-case text-slate-400">(optional, for B2B invoices)</span></label><input x-model="draft.gstin" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium uppercase focus:border-slate-900 focus:outline-none" /></div>
        <div class="col-span-2">
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Tags</label>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="t in tags" :key="t">
                    <button type="button" @click="toggleDraftTag(t)" :class="draft.tags.includes(t) ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold" x-text="t"></button>
                </template>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveCustomer()" :disabled="!draft.name?.trim() || (draft.phone || '').trim().length < 10" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-text="draft.id ? 'Save Changes' : 'Add Customer'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Adjust Loyalty Points --}}
<x-pos.dialog name="loyalty" width="max-w-sm" title="Adjust Loyalty Points">
    <div class="space-y-3 p-4">
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Points <span class="font-normal normal-case text-slate-400">(use − to deduct)</span></label>
            <input x-model="loyaltyDraft.points" data-autofocus type="number" class="pos-num h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-right text-[17px] font-black focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Reason</label>
            <input x-model="loyaltyDraft.reason" placeholder="Goodwill gesture, correction, promotion…" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <p class="rounded border border-amber-200 bg-amber-50 p-2 text-[11px] leading-snug text-amber-800">Manual point adjustments require manager permission in the live system — this preview applies them directly.</p>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="applyLoyaltyAdjust(activeCustomer)" :disabled="!Number(loyaltyDraft.points) || !loyaltyDraft.reason" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Apply</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Add / Edit Note --}}
<x-pos.dialog name="note" width="max-w-sm" title="Customer Note">
    <div class="p-4"><textarea x-model="noteDraft" data-autofocus rows="4" placeholder="Preferences, allergy notes, seating requests…" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveNote(activeCustomer)" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Save Note</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
