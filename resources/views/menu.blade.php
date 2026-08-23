<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/menu.js'])
    <script>
        window.menuModule = @json($menuModule);
        window.menuRoutes = {
            data: @json(route('menu.data')),
            items: @json(url('/menu/items')),
            categories: @json(url('/menu/categories')),
            modifiers: @json(url('/menu/modifiers')),
            combos: @json(url('/menu/combos')),
        };
    </script>
</head>
<body x-data="menuApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="menu" />

    <div class="adm-main">
        <x-admin.page-header title="Menu" subtitle="Ichapur Main Branch">
            <template x-if="tab === 'items'"><button type="button" @click="openItemForm()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Item</button></template>
            <template x-if="tab === 'categories'"><button type="button" @click="openCategoryForm()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Category</button></template>
            <template x-if="tab === 'modifiers'"><button type="button" @click="openModifierForm()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Modifier Group</button></template>
            <template x-if="tab === 'combos'"><button type="button" @click="openComboForm()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Combo</button></template>
        </x-admin.page-header>

        <x-admin.tabs :tabs="['items' => 'Items', 'categories' => 'Categories', 'modifiers' => 'Modifiers', 'stations' => 'Kitchen Stations', 'combos' => 'Combos']" active="tab" />

        {{-- ITEMS --}}
        <template x-if="tab === 'items'">
            <div class="contents">
                <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <div class="relative min-w-[190px] max-w-xs flex-1">
                        <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input x-model="query" @input="page = 1" placeholder="Search item / SKU…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                    </div>
                    <select x-model="categoryFilter" @change="page = 1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                        <option value="all">All Categories</option><template x-for="c in categories" :key="c.key"><option :value="c.key" x-text="c.label"></option></template>
                    </select>
                    @foreach (['all' => 'All', 'veg' => 'Veg', 'nonveg' => 'Non-Veg', 'egg' => 'Egg'] as $k => $l)
                        <button type="button" @click="dietFilter = '{{ $k }}'; page = 1" :class="dietFilter === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold">{{ $l }}</button>
                    @endforeach
                    <button type="button" x-show="query || categoryFilter !== 'all' || dietFilter !== 'all'" @click="clearFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900"><x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset</button>
                </div>

                <div class="adm-table-wrap bg-white">
                    <table class="adm-table">
                        <thead><tr><th>Item</th><th>Category</th><th>Type</th><th>Price</th><th>Station</th><th>Prep</th><th>Availability</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="i in pagedItems" :key="i.id">
                                <tr class="adm-row-clickable" @click="openViewItem(i)">
                                    <td><p class="font-bold text-slate-900" x-text="i.name"></p><p class="pos-num text-[10.5px] font-semibold text-slate-400" x-text="i.sku"></p></td>
                                    <td class="text-slate-600" x-text="categoryLabel(i.category)"></td>
                                    <td><x-pos.diet-mark expr="i.dietType" /></td>
                                    <td class="pos-num font-bold text-slate-900" x-text="money(i.price)"></td>
                                    <td class="text-slate-600" x-text="stationLabel(i.station)"></td>
                                    <td class="pos-num text-slate-500" x-text="i.prepTime + 'm'"></td>
                                    <td @click.stop><button type="button" @click="cycleAvailability(i)"><x-admin.badge expr="i.availability" class-expr="availabilityClass(i.availability)" label-expr="availabilityLabel(i.availability)" /></button></td>
                                    <td @click.stop>
                                        <x-admin.action-menu id-expr="i.id">
                                            <button type="button" @click="openItemForm(i)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                            <button type="button" @click="openViewItem(i)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Details</button>
                                            <button type="button" @click="duplicateItem(i)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Duplicate</button>
                                            <a href="{{ route('inventory') }}" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Recipe</a>
                                        </x-admin.action-menu>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <x-admin.empty-state icon="search" title="No items match this filter" x-show="!pagedItems.length" />
                </div>
                <x-admin.pagination total="filteredItems.length" />
            </div>
        </template>

        {{-- CATEGORIES --}}
        <template x-if="tab === 'categories'">
            <div class="pos-scroll bg-slate-100 p-3">
                <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
                    <template x-for="c in categories" :key="c.key">
                        <div class="flex items-center justify-between rounded-md border border-slate-200 bg-white p-3">
                            <div><p class="font-bold text-slate-900" x-text="c.label"></p><p class="pos-num text-[10.5px] font-semibold text-slate-400" x-text="itemCountFor(c.key) + ' items'"></p></div>
                            <button type="button" @click="openCategoryForm(c)" class="grid h-8 w-8 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900"><x-pos.icon name="edit" class="h-3.5 w-3.5" /></button>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- MODIFIERS --}}
        <template x-if="tab === 'modifiers'">
            <div class="pos-scroll bg-slate-100 p-3">
                <div class="grid gap-2.5" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <template x-for="g in modifierGroups" :key="g.id">
                        <div class="rounded-md border border-slate-200 bg-white p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <div><p class="font-bold text-slate-900" x-text="g.label"></p><p class="text-[10.5px] font-semibold text-slate-400" x-text="(g.required ? 'Required · ' : 'Optional · ') + (g.type === 'single' ? 'Choose 1' : 'Choose ' + g.min + '–' + g.max)"></p></div>
                                <button type="button" @click="openModifierForm(g)" class="grid h-8 w-8 shrink-0 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900"><x-pos.icon name="edit" class="h-3.5 w-3.5" /></button>
                            </div>
                            <div class="space-y-1"><template x-for="o in g.options" :key="o.label"><div class="flex justify-between text-[11.5px]"><span class="text-slate-700" x-text="o.label"></span><span class="pos-num font-semibold text-slate-500" x-text="o.delta ? '+' + money(o.delta) : '—'"></span></div></template></div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- STATIONS --}}
        <template x-if="tab === 'stations'">
            <div class="pos-scroll bg-slate-100 p-3">
                <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <template x-for="s in stations" :key="s.key">
                        <div class="rounded-md border border-slate-200 bg-white p-3 text-center">
                            <x-pos.icon name="chef" class="mx-auto h-6 w-6 text-slate-400" />
                            <p class="mt-1.5 font-bold text-slate-900" x-text="s.label"></p>
                            <p class="pos-num text-[10.5px] font-semibold text-slate-400" x-text="stationCountFor(s.key) + ' items routed'"></p>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- COMBOS --}}
        <template x-if="tab === 'combos'">
            <div class="pos-scroll bg-slate-100 p-3">
                <div class="grid gap-2.5" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));">
                    <template x-for="c in combos" :key="c.id">
                        <div class="rounded-md border border-slate-200 bg-white p-3">
                            <div class="mb-1.5 flex items-center justify-between">
                                <p class="font-bold text-slate-900" x-text="c.name"></p>
                                <button type="button" @click="openComboForm(c)" class="grid h-8 w-8 shrink-0 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900"><x-pos.icon name="edit" class="h-3.5 w-3.5" /></button>
                            </div>
                            <div class="space-y-0.5"><template x-for="l in c.items" :key="l.name"><p class="text-[11.5px] text-slate-600" x-text="l.qty + '× ' + l.name"></p></template></div>
                            <p class="pos-num mt-2 text-[16px] font-black text-slate-900" x-text="money(c.price)"></p>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <x-admin.toast />
    <div x-ref="overlayRoot"><x-menu.overlays /></div>
</body>
</html>
