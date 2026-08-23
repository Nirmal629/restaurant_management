<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Expenses · Royal Bengal Restaurant</title>
    <script>
        window.expenseModule = @json($expenseModule);
        window.expenseRoutes = {
            data: @json(route('expenses.data')),
            base: @json(url('/expenses')),
            export: @json(route('expenses.export')),
        };
    </script>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/expenses.js'])
</head>
<body x-data="expensesApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="expenses" />

    <div class="adm-main">
        <x-admin.page-header title="Expenses" subtitle="Ichapur Main Branch">
            <div class="flex items-center gap-2">
                <button type="button" @click="printExpenses()" class="grid h-8 w-8 place-items-center rounded-md border border-slate-300 bg-white text-slate-600 hover:border-slate-900" title="Print"><x-pos.icon name="printer" class="h-3.5 w-3.5" /></button>
                <a :href="routes.export" class="grid h-8 w-8 place-items-center rounded-md border border-slate-300 bg-white text-slate-600 hover:border-slate-900" title="Export CSV"><x-pos.icon name="download" class="h-3.5 w-3.5" /></a>
                <button type="button" @click="openCreate()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
                    <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Record Expense
                </button>
            </div>
        </x-admin.page-header>

        <div class="pos-infobar flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-4 pos-no-scrollbar">
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Today</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="money(summary.today)"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">This Month</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="money(summary.month)"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-amber-600">Pending Approval</span><span class="pos-num text-[13px] font-black text-amber-700" x-text="summary.pending"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Top Category</span><span class="text-[13px] font-black text-slate-900" x-text="summary.topCategory"></span></div>
        </div>

        <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
            <div class="relative min-w-[190px] max-w-xs flex-1"><x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input x-model="query" @input="page=1" placeholder="Search description / vendor…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <select x-model="categoryFilter" @change="page=1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none"><option value="all">All Categories</option><template x-for="c in categories" :key="c"><option x-text="c"></option></template></select>
            <select x-model="statusFilter" @change="page=1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                <option value="all">All Statuses</option><template x-for="s in ['draft','approved','rejected','paid']" :key="s"><option :value="s" x-text="statusLabel(s)"></option></template>
            </select>
            <button type="button" x-show="query || categoryFilter !== 'all' || statusFilter !== 'all'" @click="clearFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900"><x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset</button>
        </div>

        <div class="adm-table-wrap bg-white">
            <table class="adm-table">
                <thead><tr><th>Expense #</th><th>Date</th><th>Category</th><th>Description</th><th>Vendor</th><th>Method</th><th>Amount</th><th>Recorded By</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <template x-for="e in paged" :key="e.id">
                        <tr class="adm-row-clickable" @click="openDetail(e)">
                            <td class="pos-num font-bold text-slate-900" x-text="e.id"></td>
                            <td class="pos-num text-slate-500" x-text="e.date"></td>
                            <td class="text-slate-600" x-text="e.category"></td>
                            <td class="max-w-[220px] truncate text-slate-700" x-text="e.description"></td>
                            <td class="text-slate-500" x-text="e.vendor || '—'"></td>
                            <td class="text-slate-500" x-text="e.method"></td>
                            <td class="pos-num font-bold text-slate-900" x-text="money(e.amount)"></td>
                            <td class="text-slate-500" x-text="e.employee"></td>
                            <td><x-admin.badge expr="e.status" /></td>
                            <td @click.stop>
                                <x-admin.action-menu id-expr="e.id">
                                    <button type="button" @click="openDetail(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Detail</button>
                                    <button type="button" @click="openEdit(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                    <button type="button" x-show="e.status === 'draft'" @click="approve(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Approve</button>
                                    <button type="button" x-show="e.status === 'approved'" @click="markPaid(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Mark Paid</button>
                                    <button type="button" x-show="e.status === 'draft'" @click="openReject(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Reject</button>
                                    <button type="button" @click="deleteExpense(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                </x-admin.action-menu>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <x-admin.empty-state icon="receipt" title="No expenses match this filter" x-show="!paged.length" />
        </div>
        <x-admin.pagination total="filtered.length" />
    </div>

    <x-admin.toast />
    <div x-ref="overlayRoot"><x-expenses.overlays /></div>
</body>
</html>
