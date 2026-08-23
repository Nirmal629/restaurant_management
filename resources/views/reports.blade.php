<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/reports.js'])
</head>
<body x-data="reportsApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="reports" />

    <div class="adm-main">
        <x-admin.page-header title="Reports" subtitle="Ichapur Main Branch">
            <div class="flex items-center gap-1.5">
                <input x-model="dateFrom" type="date" class="pos-num h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                <span class="text-[11px] text-slate-400">to</span>
                <input x-model="dateTo" type="date" class="pos-num h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
            </div>
            <select x-model="branch" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                <option>Ichapur Main Branch</option>
            </select>
        </x-admin.page-header>

        <div class="pos-workspace">
            {{-- Report category / list nav --}}
            <aside class="w-56 shrink-0 border-r border-slate-200 bg-white">
                <nav class="pos-scroll p-2">
                    <template x-for="cat in categories" :key="cat.key">
                        <div class="mb-2">
                            <p class="mb-1 px-2 text-[9.5px] font-black uppercase tracking-[0.09em] text-slate-400" x-text="cat.label"></p>
                            <template x-for="r in cat.reports" :key="cat.key + r">
                                <button type="button" @click="selectReport(cat.key, r)"
                                        :class="activeReport === r && activeCategory === cat.key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                        class="mb-0.5 flex w-full items-center gap-1.5 rounded-md px-2.5 py-1.5 text-left text-[11.5px] font-semibold">
                                    <span class="min-w-0 flex-1 truncate" x-text="r"></span>
                                    <span x-show="isFlagship(r)" class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </nav>
            </aside>

            {{-- Report viewer --}}
            <section class="flex min-w-0 flex-1 flex-col overflow-hidden">
                <div class="pos-dock flex items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <h2 class="text-[13px] font-black text-slate-900" x-text="activeReport"></h2>
                    <span class="flex-1"></span>
                    <button type="button" @click="exportAs('excel')" class="flex h-7 items-center gap-1 rounded-md border border-slate-300 bg-white px-2 text-[10.5px] font-bold text-slate-700 hover:border-slate-900"><x-pos.icon name="download" class="h-3.5 w-3.5" /> Excel</button>
                    <button type="button" @click="exportAs('csv')" class="flex h-7 items-center gap-1 rounded-md border border-slate-300 bg-white px-2 text-[10.5px] font-bold text-slate-700 hover:border-slate-900"><x-pos.icon name="download" class="h-3.5 w-3.5" /> CSV</button>
                    <button type="button" @click="exportAs('pdf')" class="flex h-7 items-center gap-1 rounded-md border border-slate-300 bg-white px-2 text-[10.5px] font-bold text-slate-700 hover:border-slate-900"><x-pos.icon name="download" class="h-3.5 w-3.5" /> PDF</button>
                    <button type="button" @click="window.print()" class="flex h-7 items-center gap-1 rounded-md bg-slate-900 px-2 text-[10.5px] font-bold text-white hover:bg-slate-800"><x-pos.icon name="printer" class="h-3.5 w-3.5" /> Print</button>
                </div>

                <div class="pos-scroll bg-slate-100 p-3">

                    {{-- DAILY SALES --}}
                    <template x-if="activeReport === 'Daily Sales'">
                        <div>
                            <div class="grid grid-cols-3 gap-2 lg:grid-cols-6">
                                <template x-for="[k,l] in [['grossSales','Gross Sales'],['discount','Discount'],['netSales','Net Sales'],['orders','Orders'],['avgBill','Average Bill'],['guests','Guests']]" :key="k">
                                    <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9px] font-black uppercase tracking-wide text-slate-400" x-text="l"></p><p class="pos-num text-[15px] font-black text-slate-900" x-text="['orders','guests'].includes(k) ? dailySales.kpis[k] : money(dailySales.kpis[k])"></p></div>
                                </template>
                            </div>
                            <div class="mt-3 rounded-md border border-slate-200 bg-white p-3">
                                <p class="mb-2 text-[10.5px] font-black uppercase tracking-wide text-slate-500">Hourly Sales</p>
                                <div class="flex h-40 items-end gap-2">
                                    <template x-for="h in dailySales.hourly" :key="h.hour">
                                        <div class="flex flex-1 flex-col items-center justify-end gap-1">
                                            <div class="w-full rounded-t bg-brand-500" :style="'height:' + Math.round((h.sales / maxHourly) * 100) + '%'"></div>
                                            <span class="text-[9px] text-slate-400" x-text="h.hour"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="adm-table-wrap mt-3 bg-white">
                                <table class="adm-table">
                                    <thead><tr><th>Order</th><th>Time</th><th>Type</th><th>Waiter</th><th>Amount</th><th>Payment</th></tr></thead>
                                    <tbody><template x-for="t in dailySales.transactions" :key="t.code"><tr><td class="pos-num font-bold text-slate-900" x-text="t.code"></td><td class="pos-num text-slate-500" x-text="t.time"></td><td class="text-slate-600" x-text="t.type"></td><td class="text-slate-600" x-text="t.waiter"></td><td class="pos-num font-bold text-slate-900" x-text="money(t.amount)"></td><td class="text-slate-500" x-text="t.payment"></td></tr></template></tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    {{-- MENU PROFITABILITY --}}
                    <template x-if="activeReport === 'Menu Profitability'">
                        <div>
                            <div class="mb-2 flex flex-wrap gap-1.5">
                                <template x-for="q in ['Star','Plowhorse','Puzzle','Dog']" :key="q">
                                    <span class="rounded border px-2 py-1 text-[10px] font-bold" :class="quadrantClass(q)" x-text="q + ' — ' + menuRows.filter(r => quadrantOf(r) === q).length + ' items'"></span>
                                </template>
                            </div>
                            <div class="adm-table-wrap bg-white">
                                <table class="adm-table">
                                    <thead><tr><th>Item</th><th>Selling Price</th><th>Food Cost</th><th>Food Cost %</th><th>Contribution</th><th>Qty Sold</th><th>Total Contribution</th><th>Type</th></tr></thead>
                                    <tbody>
                                        <template x-for="r in menuRows" :key="r.item">
                                            <tr>
                                                <td class="font-bold text-slate-900" x-text="r.item"></td>
                                                <td class="pos-num text-slate-600" x-text="money(r.price)"></td>
                                                <td class="pos-num text-slate-600" x-text="money(r.foodCost)"></td>
                                                <td class="pos-num text-slate-500" x-text="r.foodCostPct + '%'"></td>
                                                <td class="pos-num text-slate-700" x-text="money(r.margin)"></td>
                                                <td class="pos-num text-slate-600" x-text="r.qtySold"></td>
                                                <td class="pos-num font-bold text-slate-900" x-text="money(r.totalContribution)"></td>
                                                <td><span class="rounded border px-1.5 py-px text-[9.5px] font-black uppercase" :class="quadrantClass(quadrantOf(r))" x-text="quadrantOf(r)"></span></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    {{-- REVENUE --}}
                    <template x-if="activeReport === 'Revenue'">
                        <div>
                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="[k,l] in [['revenue','Revenue'],['cogs','COGS'],['expenses','Expenses'],['grossProfit','Gross Profit']]" :key="k">
                                    <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9px] font-black uppercase tracking-wide text-slate-400" x-text="l"></p><p class="pos-num text-[15px] font-black text-slate-900" x-text="money(revenueSummary[k])"></p></div>
                                </template>
                            </div>
                            <div class="mt-3 rounded-md border border-slate-200 bg-white p-3">
                                <p class="mb-2 text-[10.5px] font-black uppercase tracking-wide text-slate-500">Revenue Trend (6 months)</p>
                                <div class="flex h-40 items-end gap-3">
                                    <template x-for="m in revenueSummary.months" :key="m.m">
                                        <div class="flex flex-1 flex-col items-center justify-end gap-1">
                                            <div class="w-full rounded-t bg-brand-500" :style="'height:' + Math.round((m.revenue / Math.max(...revenueSummary.months.map(x => x.revenue))) * 100) + '%'"></div>
                                            <span class="text-[9px] text-slate-400" x-text="m.m"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- SALES BY WAITER / ORDERS BY WAITER --}}
                    <template x-if="['Sales by Waiter', 'Orders by Waiter'].includes(activeReport)">
                        <div class="adm-table-wrap bg-white">
                            <table class="adm-table">
                                <thead><tr><th>Waiter</th><th>Orders</th><th>Sales</th><th>Average Bill</th><th>Tables Served</th><th>Discounts Given</th><th>Voids</th></tr></thead>
                                <tbody>
                                    <template x-for="w in waiterSales" :key="w.name">
                                        <tr>
                                            <td class="font-bold text-slate-900" x-text="w.name"></td>
                                            <td class="pos-num text-slate-600" x-text="w.orders"></td>
                                            <td class="pos-num font-bold text-slate-900" x-text="money(w.sales)"></td>
                                            <td class="pos-num text-slate-600" x-text="money(w.avgBill)"></td>
                                            <td class="pos-num text-slate-600" x-text="w.tables"></td>
                                            <td class="pos-num text-slate-500" x-text="money(w.discounts)"></td>
                                            <td class="pos-num text-slate-500" x-text="w.voids"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    {{-- GENERIC FALLBACK — every other report in the catalog --}}
                    <template x-if="!['Daily Sales','Menu Profitability','Revenue','Sales by Waiter','Orders by Waiter'].includes(activeReport)">
                        <div>
                            <p class="mb-2 rounded border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-[11px] font-semibold text-sky-800">Representative sample data — this report's live calculation isn't wired up yet in this design preview.</p>
                            <div class="grid grid-cols-4 gap-2">
                                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9px] font-black uppercase tracking-wide text-slate-400">Total</p><p class="pos-num text-[15px] font-black text-slate-900" x-text="money(genericTotals.total)"></p></div>
                                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9px] font-black uppercase tracking-wide text-slate-400">Count</p><p class="pos-num text-[15px] font-black text-slate-900" x-text="genericTotals.qty"></p></div>
                                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9px] font-black uppercase tracking-wide text-slate-400">Daily Average</p><p class="pos-num text-[15px] font-black text-slate-900" x-text="money(genericTotals.avg)"></p></div>
                                <div class="rounded-md border border-slate-200 bg-white p-2.5"><p class="text-[9px] font-black uppercase tracking-wide text-slate-400">Best Day</p><p class="pos-num text-[15px] font-black text-slate-900" x-text="genericTotals.best.label"></p></div>
                            </div>
                            <div class="mt-3 rounded-md border border-slate-200 bg-white p-3">
                                <div class="flex h-32 items-end gap-2">
                                    <template x-for="r in genericRows" :key="r.label">
                                        <div class="flex flex-1 flex-col items-center justify-end gap-1">
                                            <div class="w-full rounded-t bg-brand-500" :style="'height:' + Math.round((r.amount / genericTotals.best.amount) * 100) + '%'"></div>
                                            <span class="text-[9px] text-slate-400" x-text="r.label"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="adm-table-wrap mt-3 bg-white">
                                <table class="adm-table">
                                    <thead><tr><th>Date</th><th>Count</th><th>Amount</th></tr></thead>
                                    <tbody><template x-for="r in genericRows" :key="r.label"><tr><td class="font-semibold text-slate-800" x-text="r.label"></td><td class="pos-num text-slate-600" x-text="r.qty"></td><td class="pos-num font-bold text-slate-900" x-text="money(r.amount)"></td></tr></template></tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
