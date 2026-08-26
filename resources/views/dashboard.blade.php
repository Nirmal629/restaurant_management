<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/dashboard.js'])
</head>
@php
    $employee = auth()->user()?->employee?->loadMissing('role', 'branch');
@endphp
<body x-data="dashboardApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">
    <x-shell.sidebar active="dashboard" />

    <div class="adm-main">
        <x-admin.page-header title="Restaurant Dashboard" subtitle="Commercial POS / ERP command center">
            <div class="flex items-center gap-2">
                <select x-model="branch" @change="applyFilters()" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[12px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                    <template x-for="b in data.branches" :key="b"><option x-text="b"></option></template>
                </select>
                <input type="date" x-model="dateFrom" @change="applyFilters()" class="pos-num h-8 rounded-md border border-slate-300 bg-white px-2 text-[12px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                <input type="date" x-model="dateTo" @change="applyFilters()" class="pos-num hidden h-8 rounded-md border border-slate-300 bg-white px-2 text-[12px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none lg:block">
            </div>
        </x-admin.page-header>

        <main class="min-h-0 flex-1 overflow-auto p-3">
            <div x-show="error" class="mb-2 rounded-md border border-rose-300 bg-rose-50 px-3 py-2 text-[12px] font-bold text-rose-700" x-text="error"></div>

            <section class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
                <template x-for="kpi in data.kpis" :key="kpi[0]">
                    <div class="rounded-md border border-slate-200 bg-white p-3 shadow-sm" :title="kpi[0] + ' for selected branch and date range'">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-[10px] font-black uppercase tracking-wide text-slate-500" x-text="kpi[0]"></p>
                            <span class="rounded px-1.5 py-0.5 text-[9.5px] font-black" :class="kpi[3] === 'good' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" x-text="kpi[2]"></span>
                        </div>
                        <p class="pos-num mt-2 text-[22px] font-black leading-none text-slate-950" x-text="['Total Sales','Avg Order Value','Expenses','Profit'].includes(kpi[0]) ? money(kpi[1]) : kpi[1]"></p>
                    </div>
                </template>
            </section>

            <section class="mt-3 grid gap-3 xl:grid-cols-[1.35fr_0.85fr_0.8fr]">
                <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-slate-200 px-3 py-2">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Sales Trend</p>
                            <p class="text-[12px] font-semibold text-slate-500" x-text="branch"></p>
                        </div>
                        <div class="flex rounded-md bg-slate-100 p-0.5">
                            <template x-for="r in data.ranges" :key="r">
                                <button type="button" @click="setRange(r)" class="rounded px-2 py-1 text-[10.5px] font-black uppercase" :class="range === r ? 'bg-slate-900 text-white' : 'text-slate-600'" x-text="r"></button>
                            </template>
                        </div>
                    </div>
                    <div class="relative h-48 p-3">
                        <div x-show="loading" class="absolute inset-3 z-10 rounded-md bg-white/80 p-4">
                            <div class="adm-skeleton h-full"></div>
                        </div>
                        <svg viewBox="0 0 300 130" class="h-full w-full" role="img" aria-label="Sales trend chart">
                            <line x1="12" y1="112" x2="288" y2="112" stroke="#e2e8f0" />
                            <line x1="12" y1="72" x2="288" y2="72" stroke="#f1f5f9" />
                            <line x1="12" y1="32" x2="288" y2="32" stroke="#f1f5f9" />
                            <polyline :points="trendPoints()" fill="none" stroke="#0f172a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                            <template x-for="(point, index) in trendPoints().split(' ')" :key="index">
                                <circle :cx="point.split(',')[0]" :cy="point.split(',')[1]" r="3.5" fill="#10b981"></circle>
                            </template>
                        </svg>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-3 py-2">
                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Order Status</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-3">
                        <template x-for="s in data.orderStatus" :key="s[0]">
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-2" :title="s[0] + ' orders'">
                                <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full" :class="s[2]"></span><p class="text-[10.5px] font-black uppercase text-slate-600" x-text="s[0]"></p></div>
                                <p class="pos-num mt-1 text-[20px] font-black text-slate-900" x-text="s[1]"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-3 py-2">
                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Critical Alerts</p>
                    </div>
                    <div class="space-y-2 p-3">
                        <template x-for="a in data.alerts" :key="a[0]">
                            <div class="rounded-md border px-2.5 py-2" :class="statusTone(a[2])">
                                <p class="text-[11px] font-black uppercase" x-text="a[0]"></p>
                                <p class="mt-0.5 text-[11.5px] font-semibold" x-text="a[1]"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <section class="mt-3 grid gap-3 xl:grid-cols-4">
                <div class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Order Channel</p>
                    <div class="mt-2 space-y-2">
                        <template x-for="c in data.channels" :key="c[0]">
                            <div>
                                <div class="mb-1 flex justify-between text-[11.5px] font-bold"><span x-text="c[0]"></span><span class="pos-num" x-text="c[1] + ' · ' + c[2] + '%'"></span></div>
                                <div class="h-2 rounded bg-slate-100"><div class="h-2 rounded bg-slate-900" :style="'width:' + c[2] + '%'"></div></div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Payment Summary</p>
                    <div class="mt-2 space-y-2">
                        <template x-for="p in data.payments" :key="p[0]">
                            <div>
                                <div class="mb-1 flex justify-between text-[11.5px] font-bold"><span x-text="p[0]"></span><span class="pos-num" x-text="money(p[1])"></span></div>
                                <div class="h-2 rounded bg-slate-100"><div class="h-2 rounded bg-emerald-500" :style="'width:' + paymentWidth(p[1])"></div></div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Tables & Reservations</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <div class="rounded border border-slate-200 bg-slate-50 p-2"><p class="text-[9.5px] font-black uppercase text-slate-400">Occupied</p><p class="pos-num text-[18px] font-black" x-text="data.tables.occupied + '/' + data.tables.total"></p></div>
                        <div class="rounded border border-slate-200 bg-slate-50 p-2"><p class="text-[9.5px] font-black uppercase text-slate-400">Reserved</p><p class="pos-num text-[18px] font-black" x-text="data.tables.reserved"></p></div>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded bg-slate-100"><div class="h-full bg-sky-500" :style="'width:' + tablePercent('occupied') + '%'"></div></div>
                    <div class="mt-2 space-y-1">
                        <template x-for="r in data.reservations" :key="r[0] + r[1]">
                            <p class="truncate text-[11px] font-semibold text-slate-600"><span class="pos-num font-black text-slate-900" x-text="r[0]"></span> <span x-text="r[1] + ' · ' + r[2] + ' · ' + r[3] + ' guests'"></span></p>
                        </template>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Kitchen Health</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <div class="rounded border border-slate-200 bg-slate-50 p-2"><p class="text-[9.5px] font-black uppercase text-slate-400">Avg Prep</p><p class="pos-num text-[18px] font-black" x-text="data.kitchen.avgPrep + 'm'"></p></div>
                        <div class="rounded border border-rose-200 bg-rose-50 p-2"><p class="text-[9.5px] font-black uppercase text-rose-500">Delayed</p><p class="pos-num text-[18px] font-black text-rose-700" x-text="data.kitchen.delayed"></p></div>
                        <div class="rounded border border-slate-200 bg-slate-50 p-2"><p class="text-[9.5px] font-black uppercase text-slate-400">Oldest</p><p class="pos-num text-[18px] font-black" x-text="data.kitchen.oldest + 'm'"></p></div>
                        <div class="rounded border border-emerald-200 bg-emerald-50 p-2"><p class="text-[9.5px] font-black uppercase text-emerald-600">Ready</p><p class="pos-num text-[18px] font-black text-emerald-700" x-text="data.kitchen.ready"></p></div>
                    </div>
                </div>
            </section>

            <section class="mt-3 grid gap-3 xl:grid-cols-[1fr_1fr_1fr]">
                <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-3 py-2"><p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Top-Selling Items</p></div>
                    <div class="adm-table-wrap max-h-56">
                        <table class="adm-table"><thead><tr><th>Item</th><th>Qty</th><th>Sales</th></tr></thead><tbody>
                            <template x-for="i in data.topItems" :key="i[0]"><tr><td class="font-bold" x-text="i[0]"></td><td class="pos-num" x-text="i[1]"></td><td class="pos-num font-bold" x-text="money(i[2])"></td></tr></template>
                        </tbody></table>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-3 py-2"><p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Inventory Alerts</p></div>
                    <div class="adm-table-wrap max-h-56">
                        <table class="adm-table"><thead><tr><th>Item</th><th>Status</th><th>Stock</th><th>Reorder</th></tr></thead><tbody>
                            <template x-for="i in data.inventory" :key="i[0]"><tr><td class="font-bold" x-text="i[0]"></td><td><span class="rounded border px-1.5 py-px text-[9px] font-black uppercase" :class="statusTone(i[1])" x-text="i[1]"></span></td><td class="pos-num" x-text="i[2]"></td><td class="pos-num" x-text="i[3]"></td></tr></template>
                        </tbody></table>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-3 py-2"><p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Employee Performance</p></div>
                    <div class="adm-table-wrap max-h-56">
                        <table class="adm-table"><thead><tr><th>Waiter</th><th>Orders</th><th>Sales</th><th>Incentive</th></tr></thead><tbody>
                            <template x-for="e in data.employees" :key="e[0]"><tr><td class="font-bold" x-text="e[0]"></td><td class="pos-num" x-text="e[1]"></td><td class="pos-num" x-text="money(e[2])"></td><td class="pos-num font-bold text-emerald-700" x-text="money(e[3])"></td></tr></template>
                        </tbody></table>
                    </div>
                </div>
            </section>

            <section class="mt-3 grid gap-3 xl:grid-cols-[0.8fr_1.2fr]">
                <div class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Expense & Profit</p>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <div class="rounded border border-slate-200 bg-slate-50 p-2"><p class="text-[9px] font-black uppercase text-slate-400">Revenue</p><p class="pos-num text-[16px] font-black" x-text="money(52340)"></p></div>
                        <div class="rounded border border-amber-200 bg-amber-50 p-2"><p class="text-[9px] font-black uppercase text-amber-600">Expense</p><p class="pos-num text-[16px] font-black" x-text="money(18320)"></p></div>
                        <div class="rounded border border-emerald-200 bg-emerald-50 p-2"><p class="text-[9px] font-black uppercase text-emerald-600">Profit</p><p class="pos-num text-[16px] font-black text-emerald-700" x-text="money(34020)"></p></div>
                    </div>
                </div>
                <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-3 py-2"><p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Recent Orders & Activities</p></div>
                    <div class="grid gap-1.5 p-3 sm:grid-cols-2 xl:grid-cols-5">
                        <template x-for="a in data.recent" :key="a[0] + a[1]">
                            <div class="rounded-md border px-2 py-2" :class="statusTone(a[2])"><p class="pos-num text-[10px] font-black" x-text="a[0]"></p><p class="mt-0.5 text-[11px] font-semibold" x-text="a[1]"></p></div>
                        </template>
                    </div>
                </div>
            </section>

            <div x-show="!data.kpis.length" class="mt-3"><x-admin.empty-state icon="chart" title="No dashboard data" hint="Connect the dashboard API to start showing restaurant metrics." /></div>
        </main>
    </div>
</body>
</html>
