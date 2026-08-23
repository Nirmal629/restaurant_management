{{-- PaymentDrawer — mixed tender, stays inside the POS flow --}}
<x-pos.dialog name="payment" variant="drawer" width="max-w-lg" title="Take payment" tone="dark"
              subtitle="Add one or more tenders until Due reaches zero.">

    {{-- Money header: the three numbers the cashier actually reads --}}
    <div class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200 bg-white">
        <div class="px-3 py-2.5">
            <p class="text-[9.5px] font-black uppercase tracking-[0.09em] text-slate-400">Total</p>
            <p class="pos-num text-[19px] font-black leading-tight text-slate-900" x-text="money(total)"></p>
        </div>
        <div class="px-3 py-2.5">
            <p class="text-[9.5px] font-black uppercase tracking-[0.09em] text-slate-400">Paid</p>
            <p class="pos-num text-[19px] font-black leading-tight text-emerald-700" x-text="money(paid)"></p>
        </div>
        <div class="px-3 py-2.5" :class="due ? 'bg-rose-50' : 'bg-emerald-50'">
            <p class="text-[9.5px] font-black uppercase tracking-[0.09em] text-slate-400">Due</p>
            <p class="pos-num text-[19px] font-black leading-tight" :class="due ? 'text-rose-600' : 'text-emerald-700'" x-text="money(due)"></p>
        </div>
    </div>

    <div class="space-y-3 p-3">

        {{-- Method --}}
        <div class="grid grid-cols-3 gap-1.5">
            <template x-for="m in paymentMethods" :key="m.key">
                <button type="button" @click="payDraft.method = m.key"
                        :class="payDraft.method === m.key ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                        class="h-11 rounded-md border text-[12px] font-bold uppercase tracking-wide" x-text="m.label"></button>
            </template>
        </div>

        {{-- Amount --}}
        <div>
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600"
                   x-text="payDraft.method === 'cash' ? 'Amount received' : 'Amount'"></label>
            <div class="relative">
                <span class="pos-num absolute left-3 top-1/2 -translate-y-1/2 text-[19px] font-bold text-slate-400">₹</span>
                <input x-model="payDraft.amount" data-autofocus type="number" min="0" inputmode="decimal"
                       class="pos-num h-14 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-right text-[26px] font-black tracking-tight text-slate-900 focus:border-slate-900 focus:outline-none" />
            </div>

            <div class="mt-1.5 grid grid-cols-5 gap-1.5">
                <button type="button" @click="quickCash(due)"
                        class="h-10 rounded-md border border-slate-900 bg-slate-900 text-[11px] font-bold uppercase tracking-wide text-white">Exact</button>
                @foreach ([100, 500, 1000, 2000] as $note)
                    <button type="button" @click="payDraft.amount = String((Number(payDraft.amount) || 0) + {{ $note }})"
                            class="pos-num h-10 rounded-md border border-slate-300 bg-white text-[12px] font-bold text-slate-700 hover:border-slate-900">+{{ $note }}</button>
                @endforeach
            </div>

            {{-- Reference for non-cash tenders --}}
            <input x-show="payDraft.method !== 'cash'" x-model="payDraft.reference"
                   :placeholder="payDraft.method === 'upi' ? 'UPI reference / VPA' : payDraft.method === 'card' ? 'Last 4 digits / auth code' : 'Reference'"
                   class="mt-1.5 h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />

            {{-- Change due --}}
            <div x-show="payDraft.method === 'cash' && cashChange > 0"
                 class="mt-1.5 flex items-center justify-between rounded-md border-2 border-amber-400 bg-amber-50 px-3 py-2">
                <span class="text-[11.5px] font-black uppercase tracking-[0.08em] text-amber-900">Change due</span>
                <span class="pos-num text-[20px] font-black text-amber-900" x-text="money(cashChange)"></span>
            </div>

            <button type="button" @click="addPayment()" :disabled="saving || !(Number(payDraft.amount) > 0) || !due" :aria-busy="saving ? 'true' : 'false'"
                    class="mt-1.5 h-11 w-full rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                Add tender
            </button>
        </div>

        {{-- Captured tenders (mixed payment) --}}
        <div x-show="payments.length">
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Tenders</p>
            <div class="space-y-1.5">
                <template x-for="(p, i) in payments" :key="i">
                    <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-2.5 py-2">
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10.5px] font-black uppercase tracking-wide text-slate-700" x-text="p.label"></span>
                        <span class="pos-num min-w-0 flex-1 truncate text-[11px] text-slate-500" x-text="p.reference"></span>
                        <span class="pos-num text-[13.5px] font-bold text-slate-900" x-text="money(p.amount)"></span>
                        <button type="button" @click="removePayment(i)"
                                class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-400 hover:border-rose-500 hover:text-rose-600">
                            <x-pos.icon name="trash" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Post-settlement options --}}
        <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5">
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-500">On settlement</p>
            <div class="flex flex-wrap gap-3 text-[11.5px] font-semibold text-slate-700">
                <label class="flex items-center gap-1.5"><input type="checkbox" checked class="h-3.5 w-3.5 accent-slate-900"> Print bill</label>
                <label class="flex items-center gap-1.5" x-show="orderType === 'dinein'"><input type="checkbox" checked class="h-3.5 w-3.5 accent-slate-900"> Close &amp; free table</label>
                <label class="flex items-center gap-1.5" x-show="order.customer"><input type="checkbox" checked class="h-3.5 w-3.5 accent-slate-900"> Credit loyalty points</label>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex gap-1.5">
            <button type="button" @click="back()"
                    class="h-12 rounded-md border border-slate-300 bg-white px-4 text-[11.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Back</button>
            <button type="button" @click="openSplit()"
                    class="h-12 rounded-md border border-slate-300 bg-white px-4 text-[11.5px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Split</button>
            <button type="button" @click="settle()" :disabled="saving || due > 0" :aria-busy="saving ? 'true' : 'false'"
                    class="h-12 flex-1 rounded-md bg-emerald-600 text-[13px] font-black uppercase tracking-[0.08em] text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-show="due > 0" x-text="'Due ' + money(due)"></span>
                <span x-show="!due">Settle &amp; close</span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
