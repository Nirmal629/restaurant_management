<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/customers.js'])
</head>
<body x-data="customersApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="customers" />

    <div class="adm-main">
        <x-admin.page-header title="Customers" subtitle="Ichapur Main Branch">
            <button type="button" @click="openCreate()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
                <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Add Customer
            </button>
        </x-admin.page-header>

        <div class="pos-infobar flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-4 pos-no-scrollbar">
            @foreach ([['total','Total','slate'],['vip','VIP','amber'],['newThis','New','sky'],['inactive','Inactive','rose']] as [$key,$label,$tone])
                <div class="flex h-8 items-center gap-1.5 rounded-md px-2">
                    <span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">{{ $label }}</span>
                    <span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.{{ $key }}"></span>
                </div>
            @endforeach
        </div>

        <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
            <div class="relative min-w-[190px] max-w-xs flex-1">
                <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input x-model="query" @input="page = 1" placeholder="Search name / phone / email…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
            @foreach (['all' => 'All', 'new' => 'New', 'returning' => 'Returning', 'vip' => 'VIP', 'inactive' => 'Inactive', 'birthday' => 'Birthday', 'anniversary' => 'Anniversary'] as $k => $l)
                <button type="button" @click="segment = '{{ $k }}'; page = 1" :class="segment === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="rounded-md border px-2.5 py-1.5 text-[11px] font-bold">{{ $l }}</button>
            @endforeach
            <button type="button" x-show="query || segment !== 'all'" @click="clearFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900">
                <x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset
            </button>
        </div>

        <div class="adm-table-wrap bg-white">
            <template x-if="loading"><x-admin.skeleton-rows :cols="6" /></template>
            <table class="adm-table" x-show="!loading">
                <thead><tr><th>Customer</th><th>Phone</th><th>Visits</th><th>Total Spend</th><th>Avg Bill</th><th>Last Visit</th><th>Loyalty</th><th></th></tr></thead>
                <tbody>
                    <template x-for="c in paged" :key="c.id">
                        <tr class="adm-row-clickable" @click="openProfile(c)">
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-admin.avatar initials-expr="initials(c.name)" size="sm" />
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-1 truncate font-bold text-slate-900"><span x-text="c.name"></span><span x-show="c.vip" class="rounded bg-amber-100 px-1 text-[8.5px] font-bold uppercase text-amber-800">VIP</span></p>
                                    </div>
                                </div>
                            </td>
                            <td class="pos-num text-slate-600" x-text="c.phone"></td>
                            <td class="pos-num text-slate-600" x-text="c.visits"></td>
                            <td class="pos-num font-bold text-slate-900" x-text="money(c.spend)"></td>
                            <td class="pos-num text-slate-600" x-text="money(c.avgBill)"></td>
                            <td class="pos-num text-slate-500" x-text="c.lastVisit === '—' ? '—' : formatDate(c.lastVisit)"></td>
                            <td class="pos-num font-bold text-emerald-700" x-text="c.points + ' pts'"></td>
                            <td @click.stop>
                                <x-admin.action-menu id-expr="c.id">
                                    <button type="button" @click="openProfile(c)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Profile</button>
                                    <button type="button" @click="openEdit(c)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                    <button type="button" @click="toggleVip(c)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50" x-text="c.vip ? 'Unmark VIP' : 'Mark VIP'"></button>
                                    <a href="{{ route('reservations') }}" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Create Reservation</a>
                                </x-admin.action-menu>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <x-admin.empty-state icon="users" title="No customers match this filter" x-show="!loading && !paged.length" />
        </div>
        <x-admin.pagination total="filtered.length" />
    </div>

    <x-admin.toast />
    <div x-ref="overlayRoot"><x-customers.overlays /></div>
</body>
</html>
