{{-- Ingredient master --}}
<div x-show="showIngredientForm" x-cloak>
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
            <button type="button" @click="saveIngredient()" :disabled="saving || !ingredientDraft.name?.trim()" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
</div>


{{-- Supplier detail --}}
<div x-show="showSupplierDetail" x-cloak>
<x-pos.dialog name="supplierDetail" variant="drawer" width="max-w-md" title="Supplier" :subtitle="null">
    <template x-if="activeSupplier">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <p class="text-[14px] font-black text-slate-900" x-text="activeSupplier.name"></p>
                    <x-admin.badge expr="activeSupplier.status" class-expr="activeSupplier.status === 'active' ? 'border-emerald-400 bg-emerald-50 text-emerald-800' : 'border-slate-300 bg-slate-100 text-slate-500'" label-expr="activeSupplier.status === 'active' ? 'Active' : 'Inactive'" />
                </div>
                <p class="text-[11.5px] font-semibold text-slate-500" x-text="[activeSupplier.contact, activeSupplier.phone].filter(Boolean).join(' · ') || 'No contact details'"></p>
            </div>
            <div class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                <div class="col-span-2 rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Address</p><p class="text-[12px] text-slate-800" x-text="activeSupplier.address || '-'"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Email</p><p class="break-all text-[12px] font-bold text-slate-900" x-text="activeSupplier.email || '-'"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">GSTIN</p><p class="pos-num text-[12px] font-bold text-slate-900" x-text="activeSupplier.gstin || '-'"></p></div>
                <div class="rounded-md border border-amber-200 bg-amber-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-amber-700">Outstanding</p><p class="pos-num text-[13px] font-black text-amber-800" x-text="money(activeSupplier.outstanding)"></p></div>
                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Items</p><p class="pos-num text-[13px] font-black text-slate-900" x-text="(activeSupplier.items || []).length"></p></div>
            </div>
            <div class="p-3">
                <p class="mb-1.5 text-[10px] font-black uppercase tracking-[0.09em] text-slate-400">Items Supplied</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="item in (activeSupplier.items || [])" :key="item"><span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700" x-text="item"></span></template>
                    <p x-show="!(activeSupplier.items || []).length" class="text-[11px] text-slate-400">No linked ingredients.</p>
                </div>
            </div>
        </div>
    </template>
    <x-slot:footer>
        <template x-if="activeSupplier">
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" @click="openSupplierForm(activeSupplier)" class="h-9 rounded-md bg-slate-900 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Edit</button>
                <button type="button" @click="deleteSupplier(activeSupplier)" class="h-9 rounded-md border border-rose-300 bg-white text-[11.5px] font-bold text-rose-600 hover:bg-rose-50">Delete</button>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>
</div>


{{-- Supplier form --}}
<div x-show="showSupplierForm" x-cloak>
<x-pos.dialog name="supplierForm" width="max-w-lg" title="Supplier Details">
    <div class="grid grid-cols-2 gap-3 p-4">
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Supplier Name</label><input x-model="supplierDraft.name" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Contact Person</label><input x-model="supplierDraft.contact" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Phone</label><input x-model="supplierDraft.phone" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Email</label><input x-model="supplierDraft.email" type="email" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">GSTIN</label><input x-model="supplierDraft.gstin" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium uppercase focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Outstanding</label><input x-model="supplierDraft.outstanding" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Status</label><select x-model="supplierDraft.status" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Address</label><textarea x-model="supplierDraft.address" rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveSupplier()" :disabled="saving || !supplierDraft.name?.trim()" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
</div>


{{-- Adjust stock --}}
<div x-show="showAdjust" x-cloak>
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
            <button type="button" @click="confirmAdjust()" :disabled="saving || !Number(adjustDraft.qty)" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Apply</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
</div>


{{-- Recipe / BOM --}}
<div x-show="showRecipe" x-cloak>
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
            <button type="button" @click="openRecipeForm(activeRecipeItem)" class="mt-3 h-9 w-full rounded-md bg-slate-900 text-[11.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Edit Recipe</button>
        </div>
    </template>
</x-pos.dialog>
</div>

<div x-show="showRecipeForm" x-cloak>
<x-pos.dialog name="recipeForm" width="max-w-lg" title="Edit Recipe / BOM">
    <div class="space-y-3 p-4">
        <div>
            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Menu Item</label>
            <select x-model="recipeDraft.itemId" @change="selectRecipeItem()" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                <template x-for="item in menuItems" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
            </select>
        </div>
        <div class="space-y-1.5">
            <template x-for="(l, idx) in recipeDraft.lines" :key="idx">
                <div class="flex items-center gap-1.5">
                    <select x-model="l.ingredient" class="h-8 flex-1 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none">
                        <template x-for="i in ingredients" :key="i.id"><option :value="i.name" x-text="i.name"></option></template>
                    </select>
                    <input x-model="l.qty" type="number" step="0.001" class="pos-num h-8 w-20 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                    <select x-model="l.unit" class="h-8 w-24 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none">
                        <template x-for="u in units" :key="u"><option x-text="u"></option></template>
                    </select>
                    <button type="button" @click="removeRecipeLine(idx)" class="grid h-8 w-8 shrink-0 place-items-center rounded border border-rose-200 text-rose-500 hover:border-rose-500"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button>
                </div>
            </template>
        </div>
        <button type="button" @click="addRecipeLine()" class="h-8 rounded-md border border-slate-300 px-2.5 text-[11px] font-bold text-slate-700 hover:border-slate-900">Add Line</button>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveRecipe()" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save Recipe</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
</div>


{{-- Wastage entry --}}
<div x-show="showWastageForm" x-cloak>
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
            <button type="button" @click="saveWastage()" :disabled="saving || !Number(wastageDraft.qty) || !wastageDraft.reason" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-rose-600 text-[12px] font-black uppercase tracking-wide text-white hover:bg-rose-500 disabled:cursor-not-allowed disabled:bg-slate-300">Record Wastage</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
</div>


{{-- Stock count --}}
<div x-show="showCount" x-cloak>
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
            <button type="button" @click="submitStockCount()" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Submit Count</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
</div>
