<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/inventory.js'])
    <script>
        window.inventoryModule = @json($inventoryModule);
        window.inventoryRoutes = {
            data: @json(route('inventory.data')),
            ingredients: @json(url('/inventory/ingredients')),
            suppliers: @json(url('/inventory/suppliers')),
            wastage: @json(route('inventory.wastage.store')),
            wastageBase: @json(url('/inventory/wastage')),
            counts: @json(route('inventory.counts.store')),
            countsBase: @json(url('/inventory/counts')),
            recipes: @json(url('/inventory/recipes')),
            export: @json(route('inventory.export')),
        };
        window.realtimeVersionsUrl = @json(route('realtime.versions'));
        window.realtimeStreamUrl = @json(route('realtime.stream'));
    </script>
</head>
<body x-data="inventoryApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="inventory" />

    <div class="adm-main">
        <x-admin.page-header title="Inventory" subtitle="Ichapur Main Branch">
            <template x-if="tab === 'ingredients'"><button type="button" @click="openIngredientForm()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Ingredient</button></template>
            <template x-if="tab === 'suppliers'"><button type="button" @click="openSupplierForm()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Supplier</button></template>
            <template x-if="tab === 'wastage'"><button type="button" @click="openWastageForm()" class="flex h-8 items-center gap-1.5 rounded-md bg-rose-600 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-rose-500"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Record Wastage</button></template>
            <template x-if="tab === 'count'"><button type="button" @click="openStockCount()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Count</button></template>
            <button type="button" @click="printInventory()" class="flex h-8 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-[11.5px] font-bold text-slate-700 hover:border-slate-900"><x-pos.icon name="printer" class="h-3.5 w-3.5" /> Print</button>
            <a :href="routes.export" class="flex h-8 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-[11.5px] font-bold text-slate-700 hover:border-slate-900"><x-pos.icon name="download" class="h-3.5 w-3.5" /> Export</a>
        </x-admin.page-header>

        <div class="pos-infobar flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-4 pos-no-scrollbar">
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Ingredients</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.total"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-amber-600">Low Stock</span><span class="pos-num text-[13px] font-black text-amber-700" x-text="summary.low"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-rose-600">Out of Stock</span><span class="pos-num text-[13px] font-black text-rose-700" x-text="summary.out"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Stock Value</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="money(summary.value)"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Today's Wastage</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="money(summary.wastageToday)"></span></div>
        </div>

        <x-admin.tabs :tabs="['stock' => 'Stock', 'ingredients' => 'Ingredients', 'suppliers' => 'Suppliers', 'ledger' => 'Stock Ledger', 'wastage' => 'Wastage', 'count' => 'Stock Count', 'low' => 'Low Stock']" active="tab" />

        {{-- STOCK --}}
        <template x-if="tab === 'stock'">
            <div class="contents">
                <div class="pos-dock flex items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <span class="text-[10.5px] font-black uppercase tracking-wide text-slate-500">Recipes</span>
                    <button type="button" @click="openRecipeForm()" class="flex h-7 items-center gap-1 rounded-md bg-slate-900 px-2 text-[11px] font-bold text-white hover:bg-slate-800">
                        <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Recipe
                    </button>
                    <template x-for="name in Object.keys(recipes)" :key="name">
                        <button type="button" @click="openRecipe(name)" class="flex h-7 items-center gap-1 rounded-md border border-slate-300 bg-white px-2 text-[11px] font-bold text-slate-700 hover:border-slate-900">
                            <x-pos.icon name="clipboard" class="h-3.5 w-3.5" /> <span x-text="name"></span>
                        </button>
                    </template>
                </div>
                <div class="adm-table-wrap bg-white">
                <table class="adm-table">
                    <thead><tr><th>Ingredient</th><th>Current</th><th>Min</th><th>Avg Cost</th><th>Stock Value</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="i in ingredients" :key="i.id">
                            <tr>
                                <td class="font-bold text-slate-900" x-text="i.name"></td>
                                <td class="pos-num font-bold text-slate-800" x-text="i.current + ' ' + i.unit"></td>
                                <td class="pos-num text-slate-500" x-text="i.min + ' ' + i.unit"></td>
                                <td class="pos-num text-slate-600" x-text="money(i.avgCost) + '/' + i.unit"></td>
                                <td class="pos-num font-bold text-slate-900" x-text="money(i.current * i.avgCost)"></td>
                                <td><x-admin.badge expr="i.status" /></td>
                                <td @click.stop>
                                    <x-admin.action-menu id-expr="i.id">
                                        <button type="button" @click="openAdjust(i)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Adjust Stock</button>
                                        <button type="button" @click="openLedgerFor(i)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Ledger</button>
                                        <button type="button" @click="deleteIngredient(i)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                    </x-admin.action-menu>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                </div>
            </div>
        </template>

        {{-- INGREDIENTS (master data) --}}
        <template x-if="tab === 'ingredients'">
            <div class="contents">
                <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <div class="relative min-w-[190px] max-w-xs flex-1"><x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input x-model="query" @input="page=1" placeholder="Search ingredient…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
                    <select x-model="categoryFilter" @change="page=1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none"><option value="all">All Categories</option><template x-for="c in categories" :key="c"><option x-text="c"></option></template></select>
                    <button type="button" x-show="query || categoryFilter !== 'all'" @click="clearFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900"><x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset</button>
                </div>
                <div class="adm-table-wrap bg-white">
                    <table class="adm-table">
                        <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Unit</th><th>Supplier</th><th>Location</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="i in pagedIngredients" :key="i.id">
                                <tr>
                                    <td class="pos-num text-slate-500" x-text="i.id"></td>
                                    <td class="font-bold text-slate-900" x-text="i.name"></td>
                                    <td class="text-slate-600" x-text="i.category"></td>
                                    <td class="pos-num text-slate-500" x-text="i.unit"></td>
                                    <td class="text-slate-600" x-text="i.supplier"></td>
                                    <td class="text-slate-500" x-text="i.location"></td>
                                    <td><x-admin.badge expr="i.status" /></td>
                                    <td @click.stop>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="openIngredientForm(i)" class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900"><x-pos.icon name="edit" class="h-3.5 w-3.5" /></button>
                                            <button type="button" @click="deleteIngredient(i)" class="grid h-7 w-7 place-items-center rounded border border-rose-200 text-rose-500 hover:border-rose-500"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <x-admin.empty-state icon="box" title="No ingredients match this filter" x-show="!pagedIngredients.length" />
                </div>
                <x-admin.pagination total="filteredIngredients.length" />
            </div>
        </template>

        {{-- SUPPLIERS --}}
        <template x-if="tab === 'suppliers'">
            <div class="contents">
                <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <div class="relative min-w-[190px] max-w-xs flex-1"><x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input x-model="query" @input="page=1" placeholder="Search supplier..." class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
                    <select x-model="supplierStatusFilter" @change="page=1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button type="button" x-show="query || supplierStatusFilter !== 'all'" @click="clearSupplierFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900"><x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset</button>
                </div>
                <div class="adm-table-wrap bg-white">
                    <table class="adm-table">
                        <thead><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Email</th><th>GSTIN</th><th>Items</th><th>Outstanding</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="s in pagedSuppliers" :key="s.id">
                                <tr class="adm-row-clickable" @click="openSupplierDetail(s)">
                                    <td class="font-bold text-slate-900" x-text="s.name"></td>
                                    <td class="text-slate-600" x-text="s.contact || '-'"></td>
                                    <td class="pos-num text-slate-500" x-text="s.phone || '-'"></td>
                                    <td class="text-slate-500" x-text="s.email || '-'"></td>
                                    <td class="pos-num text-slate-500" x-text="s.gstin || '-'"></td>
                                    <td class="pos-num text-slate-600" x-text="(s.items || []).length"></td>
                                    <td class="pos-num font-bold" :class="Number(s.outstanding) ? 'text-amber-700' : 'text-slate-400'" x-text="money(s.outstanding)"></td>
                                    <td><x-admin.badge expr="s.status" class-expr="s.status === 'active' ? 'border-emerald-400 bg-emerald-50 text-emerald-800' : 'border-slate-300 bg-slate-100 text-slate-500'" label-expr="s.status === 'active' ? 'Active' : 'Inactive'" /></td>
                                    <td @click.stop>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="openSupplierDetail(s)" class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900"><x-pos.icon name="eye" class="h-3.5 w-3.5" /></button>
                                            <button type="button" @click="openSupplierForm(s)" class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900"><x-pos.icon name="edit" class="h-3.5 w-3.5" /></button>
                                            <button type="button" @click="deleteSupplier(s)" class="grid h-7 w-7 place-items-center rounded border border-rose-200 text-rose-500 hover:border-rose-500"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <x-admin.empty-state icon="truck" title="No suppliers match this filter" x-show="!pagedSuppliers.length" />
                </div>
                <x-admin.pagination total="filteredSuppliers.length" />
            </div>
        </template>

        {{-- LEDGER --}}
        <template x-if="tab === 'ledger'">
            <div class="contents">
                <div class="pos-dock flex items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <div class="relative min-w-[190px] max-w-xs"><x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input x-model="query" placeholder="Filter by ingredient…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
                    <button type="button" x-show="query" @click="query = ''" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900"><x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset</button>
                </div>
                <div class="adm-table-wrap bg-white">
                    <table class="adm-table">
                        <thead><tr><th>Date</th><th>Ingredient</th><th>Type</th><th>Reference</th><th>Previous</th><th>Change</th><th>New Stock</th><th>User</th></tr></thead>
                        <tbody>
                            <template x-for="(l, i) in filteredLedger" :key="i">
                                <tr>
                                    <td class="pos-num text-slate-500" x-text="l.at"></td>
                                    <td class="font-semibold text-slate-800" x-text="l.ingredient"></td>
                                    <td><span class="rounded border border-slate-300 bg-slate-100 px-1.5 py-px text-[9.5px] font-black uppercase tracking-wide text-slate-600" x-text="l.type"></span></td>
                                    <td class="pos-num text-slate-500" x-text="l.ref"></td>
                                    <td class="pos-num text-slate-500" x-text="l.prev"></td>
                                    <td class="pos-num font-bold" :class="l.change < 0 ? 'text-rose-600' : 'text-emerald-700'" x-text="(l.change > 0 ? '+' : '') + l.change"></td>
                                    <td class="pos-num font-bold text-slate-900" x-text="l.next"></td>
                                    <td class="text-slate-500" x-text="l.user"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <x-admin.empty-state icon="clipboard" title="No ledger entries for this ingredient" x-show="!filteredLedger.length" />
                </div>
            </div>
        </template>

        {{-- WASTAGE --}}
        <template x-if="tab === 'wastage'">
            <div class="adm-table-wrap bg-white">
                <table class="adm-table">
                    <thead><tr><th>Wastage #</th><th>Ingredient</th><th>Qty</th><th>Reason</th><th>Cost</th><th>Employee</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="w in wastage" :key="w.id">
                            <tr>
                                <td class="pos-num font-bold text-slate-900" x-text="w.id"></td>
                                <td class="font-semibold text-slate-800" x-text="w.ingredient"></td>
                                <td class="pos-num text-slate-600" x-text="w.qty + ' ' + w.unit"></td>
                                <td class="text-slate-600" x-text="w.reason"></td>
                                <td class="pos-num font-bold text-rose-700" x-text="money(w.cost)"></td>
                                <td class="text-slate-600" x-text="w.employee"></td>
                                <td class="pos-num text-slate-500" x-text="w.date"></td>
                                <td><button type="button" @click="deleteWastage(w)" class="grid h-7 w-7 place-items-center rounded border border-rose-200 text-rose-500 hover:border-rose-500"><x-pos.icon name="trash" class="h-3.5 w-3.5" /></button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <x-admin.empty-state icon="alert" title="No wastage recorded" x-show="!wastage.length" />
            </div>
        </template>

        {{-- STOCK COUNT --}}
        <template x-if="tab === 'count'">
            <div class="pos-scroll bg-slate-100 p-3">
                <div class="space-y-2">
                    <template x-for="s in stockCounts" :key="s.id">
                        <div class="rounded-md border border-slate-200 bg-white p-3">
                            <div class="flex items-center justify-between">
                                <p class="pos-num font-bold text-slate-900" x-text="s.id"></p>
                                <span class="rounded border border-emerald-400 bg-emerald-50 px-1.5 py-px text-[9.5px] font-black uppercase tracking-wide text-emerald-800" x-text="s.status"></span>
                            </div>
                            <p class="text-[10.5px] font-semibold text-slate-400" x-text="s.date + ' · ' + s.by"></p>
                                <p class="mt-1 text-[11.5px] text-slate-500" x-text="s.lines.filter(l => varianceOf(l) !== 0).length + ' line(s) with variance out of ' + s.lines.length"></p>
                                <button type="button" @click="deleteStockCount(s)" class="mt-2 h-8 rounded-md border border-rose-200 px-2.5 text-[11px] font-bold text-rose-600 hover:border-rose-500">Delete Count</button>
                        </div>
                    </template>
                </div>
                <x-admin.empty-state icon="clipboard" title="No stock counts yet" hint="Start a new physical count to reconcile system stock with what's on the shelf." x-show="!stockCounts.length" />
            </div>
        </template>

        {{-- LOW STOCK --}}
        <template x-if="tab === 'low'">
            <div class="adm-table-wrap bg-white">
                <table class="adm-table">
                    <thead><tr><th>Ingredient</th><th>Current</th><th>Minimum</th><th>Reorder Level</th><th>Supplier</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="i in lowStockList" :key="i.id">
                            <tr>
                                <td class="font-bold text-slate-900" x-text="i.name"></td>
                                <td class="pos-num font-bold text-rose-600" x-text="i.current + ' ' + i.unit"></td>
                                <td class="pos-num text-slate-500" x-text="i.min + ' ' + i.unit"></td>
                                <td class="pos-num text-slate-500" x-text="i.reorder + ' ' + i.unit"></td>
                                <td class="text-slate-600" x-text="i.supplier"></td>
                                <td><x-admin.badge expr="i.status" /></td>
                                <td @click.stop><a :href="poUrlFor(i)" class="rounded-md bg-slate-900 px-2.5 py-1.5 text-[10.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800">Create PO</a></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <x-admin.empty-state icon="check" title="Nothing needs reordering right now" x-show="!lowStockList.length" />
            </div>
        </template>
    </div>

    <x-admin.toast />
    <div x-ref="overlayRoot"><x-inventory.overlays /></div>
</body>
</html>
