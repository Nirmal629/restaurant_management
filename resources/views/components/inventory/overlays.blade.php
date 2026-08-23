{{-- Ingredient master --}}
<x-pos.dialog name="ingredientForm" width="max-w-lg" title="Ingredient">
    <div class="grid grid-cols-2 gap-3 p-4">
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Ingredient Name</label><input x-model="ingredientDraft.name" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Category</label><select x-model="ingredientDraft.category" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="c in categories" :key="c"><option x-text="c"></option></template></select></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Unit</label><select x-model="ingredientDraft.unit" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="u in units" :key="u"><option x-text="u"></option></template></select></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Current Stock</label><input x-model="ingredientDraft.current" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Minimum Stock</label><input x-model="ingredientDraft.min" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Reorder Level</label><input x-model="ingredientDraft.reorder" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Average Cost</label><input x-model="ingredientDraft.avgCost" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Supplier</label><select x-model="ingredientDraft.supplier" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="s in suppliers" :key="s"><option x-text="s"></option></template></select></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Storage Location</label><select x-model="ingredientDraft.location" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="l in locations" :key="l"><option x-text="l"></option></template></select></div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveIngredient()" :disabled="!ingredientDraft.name?.trim()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Adjust stock --}}
<x-pos.dialog name="adjust" width="max-w-sm" title="Adjust Stock">
    <div class="space-y-3 p-4">
        <p class="text-[13px] font-bold text-slate-900" x-text="ingredient(adjustDraft.id)?.name"></p>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Transaction Type</label>
            <select x-model="adjustDraft.type" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="t in txTypes" :key="t"><option x-text="t"></option></template></select>
        </div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Quantity <span class="font-normal normal-case text-slate-400">(use − to reduce)</span></label>
            <input x-model="adjustDraft.qty" data-autofocus type="number" class="pos-num h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-right text-[17px] font-black focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Reference / Reason</label><input x-model="adjustDraft.reason" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="confirmAdjust()" :disabled="!Number(adjustDraft.qty)" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Apply</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Recipe / BOM --}}
<x-pos.dialog name="recipe" width="max-w-md" title="Recipe / BOM" :subtitle="null">
    <template x-if="recipes[activeRecipeItem]">
        <div class="p-4">
            <p class="text-[14px] font-black text-slate-900" x-text="activeRecipeItem"></p>
            <div class="mt-2 space-y-1.5">
                <template x-for="l in recipes[activeRecipeItem].lines" :key="l.ingredient">
                    <div class="flex justify-between rounded-md border border-slate-200 bg-white p-2 text-[12px]">
                        <span class="font-semibold text-slate-700" x-text="l.ingredient"></span>
                        <span class="pos-num font-bold text-slate-900" x-text="recipeQtyLabel(l)"></span>
                    </div>
                </template>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2">
                <div class="rounded-md border border-slate-200 bg-slate-50 p-2 text-center"><p class="text-[9px] font-black uppercase text-slate-400">Est. Cost</p><p class="pos-num text-[13px] font-black text-slate-900" x-text="money(recipeCost)"></p></div>
                <div class="rounded-md border border-slate-200 bg-slate-50 p-2 text-center"><p class="text-[9px] font-black uppercase text-slate-400">Food Cost %</p><p class="pos-num text-[13px] font-black text-slate-900" x-text="recipeFoodCostPct + '%'"></p></div>
                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-2 text-center"><p class="text-[9px] font-black uppercase text-emerald-700">Margin</p><p class="pos-num text-[13px] font-black text-emerald-800" x-text="money(recipes[activeRecipeItem].sellPrice - recipeCost)"></p></div>
            </div>
        </div>
    </template>
</x-pos.dialog>


{{-- Wastage entry --}}
<x-pos.dialog name="wastageForm" width="max-w-sm" title="Record Wastage">
    <div class="space-y-3 p-4">
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Ingredient</label>
            <select x-model="wastageDraft.ingredient" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="i in ingredients" :key="i.id"><option :value="i.name" x-text="i.name"></option></template></select>
        </div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Quantity</label><input x-model="wastageDraft.qty" data-autofocus type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div>
            <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-wide text-slate-600">Reason</p>
            <div class="flex flex-wrap gap-1.5"><template x-for="r in wastageReasons" :key="r"><button type="button" @click="wastageDraft.reason = r" :class="wastageDraft.reason === r ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold" x-text="r"></button></template></div>
        </div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Notes</label><textarea x-model="wastageDraft.notes" rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveWastage()" :disabled="!Number(wastageDraft.qty) || !wastageDraft.reason" class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Record Wastage</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Stock count --}}
<x-pos.dialog name="count" width="max-w-2xl" title="Physical Stock Count" :subtitle="null">
    <template x-if="countDraft">
        <div class="p-3">
            <div class="adm-table-wrap" style="max-height: 60vh;">
                <table class="adm-table">
                    <thead><tr><th>Ingredient</th><th>System Qty</th><th>Physical Qty</th><th>Variance</th><th>Variance Value</th><th>Reason</th></tr></thead>
                    <tbody>
                        <template x-for="(l, i) in countDraft.lines" :key="i">
                            <tr>
                                <td class="font-semibold text-slate-800" x-text="l.ingredient"></td>
                                <td class="pos-num text-slate-500" x-text="l.system"></td>
                                <td><input x-model.number="l.physical" type="number" class="pos-num h-8 w-20 rounded border border-slate-300 px-2 text-[12px] font-bold focus:border-slate-900 focus:outline-none" /></td>
                                <td class="pos-num font-bold" :class="varianceOf(l) === 0 ? 'text-slate-400' : varianceOf(l) < 0 ? 'text-rose-600' : 'text-emerald-700'" x-text="(varianceOf(l) > 0 ? '+' : '') + varianceOf(l)"></td>
                                <td class="pos-num text-slate-500" x-text="money(varianceValueOf(l))"></td>
                                <td><input x-model="l.reason" placeholder="Optional" class="h-8 w-full rounded border border-slate-300 px-2 text-[11.5px] focus:border-slate-900 focus:outline-none" /></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="submitStockCount()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Submit Count</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
