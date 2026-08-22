{{-- LoyaltyRedeemModal — deliberately quiet; never the main focus of the screen. --}}
<x-pos.dialog name="loyalty" width="max-w-sm" title="Redeem Loyalty Points">
    <div class="space-y-3 p-4">
        <div class="flex items-center justify-between rounded-md border border-slate-300 bg-slate-50 p-3">
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Available Points</p>
                <p class="pos-num text-[22px] font-black text-slate-900" x-text="customer.loyalty.points"></p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Value</p>
                <p class="pos-num text-[16px] font-bold text-slate-700" x-text="money(customer.loyalty.points * customer.loyalty.valuePerPoint)"></p>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Points to redeem</label>
            <input x-model="loyaltyDraft.points" data-autofocus type="number" min="0" :max="customer.loyalty.points"
                   class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white px-3 text-right text-[20px] font-black focus:border-slate-900 focus:outline-none" />
            <div class="mt-1.5 flex gap-1.5">
                <button type="button" @click="loyaltyDraft.points = Math.floor(customer.loyalty.points / 2)" class="h-8 flex-1 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">Half</button>
                <button type="button" @click="loyaltyDraft.points = customer.loyalty.points" class="h-8 flex-1 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">All</button>
            </div>
        </div>

        <div class="rounded-md border border-slate-300 bg-slate-50 p-3">
            <div class="flex justify-between text-[12px]"><span class="text-slate-500">Discount</span><span class="pos-num font-bold text-emerald-700" x-text="money(loyaltyPreviewAmount)"></span></div>
            <div class="mt-1 flex justify-between text-[12px]"><span class="text-slate-500">Remaining points</span><span class="pos-num font-bold text-slate-800" x-text="customer.loyalty.points - (Number(loyaltyDraft.points) || 0)"></span></div>
        </div>
        <p class="text-[10px] text-slate-400">Minimum redemption, maximum % of bill, and expiry rules are configurable — this preview applies none of them.</p>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" x-show="loyaltyRedeemed" @click="clearLoyalty(); back()" class="h-10 rounded-md border border-slate-300 bg-white px-3 text-[12px] font-bold text-rose-600 hover:border-rose-500">Remove</button>
            <span class="flex-1"></span>
            <button type="button" @click="back()" class="h-10 rounded-md border border-slate-300 bg-white px-4 text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="redeemLoyalty()" :disabled="!(Number(loyaltyDraft.points) > 0)" class="h-10 rounded-md bg-slate-900 px-5 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Redeem</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
