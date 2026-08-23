{{-- Create / Edit Menu Item --}}
<x-pos.dialog name="itemForm" width="max-w-2xl" title="Menu Item">
    <div class="grid grid-cols-2 gap-3 p-4">
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Item Name</label><input x-model="itemDraft.name" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Short Name</label><input x-model="itemDraft.shortName" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">SKU / Item Code</label><input x-model="itemDraft.sku" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium uppercase focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Category</label>
            <select x-model="itemDraft.category" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="c in categories" :key="c.key"><option :value="c.key" x-text="c.label"></option></template></select>
        </div>

        <div>
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Food Type</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach (['veg' => 'Veg', 'nonveg' => 'Non-Veg', 'egg' => 'Egg'] as $k => $l)
                    <button type="button" @click="itemDraft.dietType = '{{ $k }}'" :class="itemDraft.dietType === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="h-9 rounded-md border text-[11px] font-bold">{{ $l }}</button>
                @endforeach
            </div>
        </div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Base Price</label>
            <div class="relative"><span class="pos-num absolute left-2.5 top-1/2 -translate-y-1/2 text-[13px] font-bold text-slate-400">₹</span><input x-model="itemDraft.price" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white pl-6 pr-2.5 text-[12.5px] font-bold focus:border-slate-900 focus:outline-none" /></div>
        </div>

        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Tax Profile</label>
            <select x-model="itemDraft.taxProfile" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="t in taxProfiles" :key="t"><option x-text="t"></option></template></select>
        </div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Preparation Time (min)</label><input x-model="itemDraft.prepTime" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Kitchen Station</label>
            <select x-model="itemDraft.station" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="s in stations" :key="s.key"><option :value="s.key" x-text="s.label"></option></template></select>
        </div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Availability</label>
            <select x-model="itemDraft.availability" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none">
                <option value="available">Available</option><option value="sold_out">Sold Out</option><option value="temp_unavailable">Temporarily Unavailable</option>
            </select>
        </div>

        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Description</label><textarea x-model="itemDraft.description" rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>

        <div class="col-span-2 flex flex-wrap gap-4">
            <label class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-700"><input type="checkbox" x-model="itemDraft.featured" class="h-4 w-4 accent-slate-900"> Featured</label>
            <label class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-700"><input type="checkbox" x-model="itemDraft.popular" class="h-4 w-4 accent-slate-900"> Favourite / Popular</label>
            <label class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-700"><input type="checkbox" x-model="itemDraft.stockTracked" class="h-4 w-4 accent-slate-900"> Track Stock (link recipe in Inventory)</label>
        </div>

        {{-- Variants --}}
        <div class="col-span-2 rounded-md border border-slate-200 bg-slate-50 p-3">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-[11px] font-black uppercase tracking-wide text-slate-600">Variants</p>
                <button type="button" @click="addVariant()" class="flex items-center gap-1 text-[11px] font-bold text-slate-600 hover:text-slate-900"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Add Variant</button>
            </div>
            <div class="space-y-1.5">
                <template x-for="(v, i) in itemDraft.variants" :key="i">
                    <div class="flex items-center gap-1.5">
                        <input x-model="v.label" placeholder="Label (e.g. Large)" class="h-8 flex-1 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <input x-model="v.price" type="number" placeholder="Price" class="pos-num h-8 w-24 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <button type="button" @click="removeVariant(i)" class="grid h-8 w-8 shrink-0 place-items-center rounded border border-slate-200 text-slate-400 hover:border-rose-400 hover:text-rose-600"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button>
                    </div>
                </template>
                <p x-show="!itemDraft.variants.length" class="text-[11px] text-slate-400">No variants — single fixed price.</p>
            </div>
        </div>

        {{-- Modifier groups --}}
        <div class="col-span-2">
            <p class="mb-1.5 text-[11px] font-black uppercase tracking-wide text-slate-600">Modifiers</p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="g in modifierGroups" :key="g.id">
                    <button type="button" @click="toggleDraftModifier(g.id)" :class="itemDraft.modifierGroupIds.includes(g.id) ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold" x-text="g.label"></button>
                </template>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveItem()" :disabled="!itemDraft.name?.trim() || !itemDraft.sku?.trim() || !itemDraft.price" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-text="itemDraft.id ? 'Save Changes' : 'Add Item'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- View item (variants/modifiers read-only) --}}
<x-pos.dialog name="itemView" width="max-w-md" title="Item Details" :subtitle="null">
    <template x-if="activeItem">
        <div class="space-y-3 p-4">
            <div class="flex items-center gap-2">
                <p class="text-[15px] font-black text-slate-900" x-text="activeItem.name"></p>
                <x-admin.badge expr="activeItem.availability" class-expr="availabilityClass(activeItem.availability)" label-expr="availabilityLabel(activeItem.availability)" />
            </div>
            <p class="pos-num text-[11.5px] font-semibold text-slate-500" x-text="activeItem.sku + ' · ' + categoryLabel(activeItem.category) + ' · ' + stationLabel(activeItem.station)"></p>
            <p class="pos-num text-[20px] font-black text-slate-900" x-text="money(activeItem.price)"></p>
            <p x-show="activeItem.description" class="text-[12px] text-slate-600" x-text="activeItem.description"></p>

            <div x-show="activeItem.variants.length">
                <p class="mb-1 text-[10px] font-black uppercase tracking-wide text-slate-400">Variants</p>
                <div class="space-y-1"><template x-for="v in activeItem.variants" :key="v.label"><div class="flex justify-between text-[12px]"><span class="text-slate-700" x-text="v.label"></span><span class="pos-num font-bold text-slate-900" x-text="money(v.price)"></span></div></template></div>
            </div>
            <div x-show="activeItem.modifierGroupIds.length">
                <p class="mb-1 text-[10px] font-black uppercase tracking-wide text-slate-400">Modifiers</p>
                <div class="flex flex-wrap gap-1"><template x-for="id in activeItem.modifierGroupIds" :key="id"><span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700" x-text="modifierGroups.find(g => g.id === id)?.label"></span></template></div>
            </div>
        </div>
    </template>
</x-pos.dialog>


{{-- Category --}}
<x-pos.dialog name="categoryForm" width="max-w-sm" title="Category">
    <div class="p-4"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Name</label><input x-model="categoryDraft.label" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveCategory()" :disabled="!categoryDraft.label?.trim()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Modifier group --}}
<x-pos.dialog name="modifierForm" width="max-w-lg" title="Modifier Group">
    <div class="space-y-3 p-4">
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Group Name</label><input x-model="modifierDraft.label" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-wide text-slate-600">Selection</p>
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" @click="modifierDraft.type = 'single'" :class="modifierDraft.type === 'single' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="h-9 rounded-md border text-[11px] font-bold">Single</button>
                    <button type="button" @click="modifierDraft.type = 'multi'" :class="modifierDraft.type === 'multi' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="h-9 rounded-md border text-[11px] font-bold">Multiple</button>
                </div>
            </div>
            <div>
                <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-wide text-slate-600">Requirement</p>
                <label class="flex h-9 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 text-[11.5px] font-semibold text-slate-700"><input type="checkbox" x-model="modifierDraft.required" class="h-4 w-4 accent-slate-900"> Required</label>
            </div>
            <div x-show="modifierDraft.type === 'multi'"><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Min selection</label><input x-model="modifierDraft.min" type="number" class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <div x-show="modifierDraft.type === 'multi'"><label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Max selection</label><input x-model="modifierDraft.max" type="number" class="pos-num h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-600">Options</p>
                <button type="button" @click="addModifierOption()" class="flex items-center gap-1 text-[11px] font-bold text-slate-600 hover:text-slate-900"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Add Option</button>
            </div>
            <div class="space-y-1.5">
                <template x-for="(o, i) in modifierDraft.options" :key="i">
                    <div class="flex items-center gap-1.5">
                        <input x-model="o.label" placeholder="Option label" class="h-8 flex-1 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <input x-model="o.delta" type="number" placeholder="+₹" class="pos-num h-8 w-20 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <button type="button" @click="removeModifierOption(i)" class="grid h-8 w-8 shrink-0 place-items-center rounded border border-slate-200 text-slate-400 hover:border-rose-400 hover:text-rose-600"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveModifier()" :disabled="!modifierDraft.label?.trim()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>


{{-- Combo builder --}}
<x-pos.dialog name="comboForm" width="max-w-lg" title="Combo Builder">
    <div class="space-y-3 p-4">
        <div class="grid grid-cols-2 gap-3">
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Combo Name</label><input x-model="comboDraft.name" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Combo Price</label><input x-model="comboDraft.price" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-bold focus:border-slate-900 focus:outline-none" /></div>
        </div>
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-600">Items in Combo</p>
                <button type="button" @click="addComboLine()" class="flex items-center gap-1 text-[11px] font-bold text-slate-600 hover:text-slate-900"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Add Item</button>
            </div>
            <div class="space-y-1.5">
                <template x-for="(l, i) in comboDraft.items" :key="i">
                    <div class="flex items-center gap-1.5">
                        <select x-model="l.name" class="h-8 flex-1 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none">
                            <option value="">Select item…</option>
                            <template x-for="it in items" :key="it.id"><option :value="it.name" x-text="it.name"></option></template>
                        </select>
                        <input x-model="l.qty" type="number" min="1" class="pos-num h-8 w-16 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                        <button type="button" @click="removeComboLine(i)" class="grid h-8 w-8 shrink-0 place-items-center rounded border border-slate-200 text-slate-400 hover:border-rose-400 hover:text-rose-600"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveCombo()" :disabled="!comboDraft.name?.trim() || !comboDraft.price" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save Combo</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
