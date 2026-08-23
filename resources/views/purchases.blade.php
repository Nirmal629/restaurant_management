<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purchases · Royal Bengal Restaurant</title>
    <script>
        window.purchaseModule = @json($purchaseModule);
        window.purchaseRoutes = {
            data: @json(route('purchases.data')),
            orders: @json(url('/purchases/orders')),
            receipts: @json(url('/purchases/receipts')),
            export: @json(route('purchases.export')),
        };
    </script>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/purchases.js'])
</head>
<body x-data="purchasesApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="purchases" />

    <div class="adm-main">
        <x-admin.page-header title="Purchases" subtitle="Ichapur Main Branch">
            <div class="flex items-center gap-2">
                <button type="button" @click="printPurchases()" class="grid h-8 w-8 place-items-center rounded-md border border-slate-300 bg-white text-slate-600 hover:border-slate-900" title="Print"><x-pos.icon name="printer" class="h-3.5 w-3.5" /></button>
                <a :href="routes.export" class="grid h-8 w-8 place-items-center rounded-md border border-slate-300 bg-white text-slate-600 hover:border-slate-900" title="Export CSV"><x-pos.icon name="download" class="h-3.5 w-3.5" /></a>
                <template x-if="tab === 'po'"><button type="button" @click="openCreatePO()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800"><x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Purchase Order</button></template>
            </div>
        </x-admin.page-header>

        <div class="pos-infobar flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-4 pos-no-scrollbar">
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Open Orders</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.open"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-amber-600">Pending Approval</span><span class="pos-num text-[13px] font-black text-amber-700" x-text="summary.pendingApproval"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Open Value</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="money(summary.value)"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Supplier Outstanding</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="money(summary.outstanding)"></span></div>
        </div>

        <x-admin.tabs :tabs="['po' => 'Purchase Orders', 'grn' => 'Goods Receipts', 'suppliers' => 'Suppliers']" active="tab" />

        {{-- PURCHASE ORDERS --}}
        <template x-if="tab === 'po'">
            <div class="contents">
                <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <div class="relative min-w-[190px] max-w-xs flex-1"><x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input x-model="query" @input="page=1" placeholder="Search PO / supplier…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
                    <select x-model="statusFilter" @change="page=1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                        <option value="all">All Statuses</option>
                        <template x-for="s in ['draft','approval_pending','approved','ordered','partially_received','received','cancelled']" :key="s"><option :value="s" x-text="statusLabel(s)"></option></template>
                    </select>
                    <button type="button" x-show="query || statusFilter !== 'all'" @click="clearFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900"><x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset</button>
                </div>
                <div class="adm-table-wrap bg-white">
                    <table class="adm-table">
                        <thead><tr><th>PO Number</th><th>Supplier</th><th>Date</th><th>Items</th><th>Amount</th><th>Expected Delivery</th><th>Status</th><th>Created By</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="o in pagedOrders" :key="o.id">
                                <tr class="adm-row-clickable" @click="openDetail(o)">
                                    <td class="pos-num font-bold text-slate-900" x-text="o.id"></td>
                                    <td class="font-semibold text-slate-800" x-text="o.supplier"></td>
                                    <td class="pos-num text-slate-500" x-text="o.date"></td>
                                    <td class="pos-num text-slate-600" x-text="o.items.length"></td>
                                    <td class="pos-num font-bold text-slate-900" x-text="money(poTotal(o))"></td>
                                    <td class="pos-num text-slate-500" x-text="o.expectedDelivery"></td>
                                    <td><x-admin.badge expr="o.status" /></td>
                                    <td class="text-slate-500" x-text="o.createdBy"></td>
                                    <td @click.stop>
                                        <x-admin.action-menu id-expr="o.id">
                                            <button type="button" @click="openDetail(o)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Detail</button>
                                            <button type="button" x-show="o.status === 'draft'" @click="requestApproval(o)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Submit for Approval</button>
                                            <button type="button" x-show="o.status === 'approval_pending'" @click="openApprove(o)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Approve</button>
                                            <button type="button" x-show="o.status === 'approved'" @click="markOrdered(o)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Mark Ordered</button>
                                            <button type="button" x-show="['ordered','partially_received'].includes(o.status)" @click="openReceiveGoods(o)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Receive Goods</button>
                                            <button type="button" x-show="!['received','cancelled'].includes(o.status)" @click="cancelPO(o)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Cancel</button>
                                            <button type="button" @click="deletePO(o)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                        </x-admin.action-menu>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <x-admin.empty-state icon="truck" title="No purchase orders match this filter" x-show="!pagedOrders.length" />
                </div>
                <x-admin.pagination total="filteredOrders.length" />
            </div>
        </template>

        {{-- GOODS RECEIPTS --}}
        <template x-if="tab === 'grn'">
            <div class="adm-table-wrap bg-white">
                <table class="adm-table">
                    <thead><tr><th>GRN Number</th><th>PO Reference</th><th>Supplier</th><th>Invoice #</th><th>Received Date</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="g in receipts" :key="g.id">
                            <tr class="adm-row-clickable" @click="openGrnDetail(g)">
                                <td class="pos-num font-bold text-slate-900" x-text="g.id"></td>
                                <td class="pos-num text-slate-600" x-text="g.poRef"></td>
                                <td class="font-semibold text-slate-800" x-text="g.supplier"></td>
                                <td class="pos-num text-slate-500" x-text="g.invoiceNumber"></td>
                                <td class="pos-num text-slate-500" x-text="g.receivedDate"></td>
                                <td><x-pos.icon name="chevron-right" class="h-4 w-4 text-slate-300" /></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <x-admin.empty-state icon="truck" title="No goods receipts yet" x-show="!receipts.length" />
            </div>
        </template>

        {{-- SUPPLIERS --}}
        <template x-if="tab === 'suppliers'">
            <div class="adm-table-wrap bg-white">
                <table class="adm-table">
                    <thead><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>GSTIN</th><th>Items Supplied</th><th>Outstanding</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="s in suppliers" :key="s.id">
                            <tr class="adm-row-clickable" @click="openSupplierDetail(s)">
                                <td class="font-bold text-slate-900" x-text="s.name"></td>
                                <td class="text-slate-600" x-text="s.contact"></td>
                                <td class="pos-num text-slate-500" x-text="s.phone"></td>
                                <td class="pos-num text-slate-500" x-text="s.gstin || '—'"></td>
                                <td class="text-slate-500" x-text="s.items.length + ' items'"></td>
                                <td class="pos-num font-bold" :class="s.outstanding ? 'text-amber-700' : 'text-slate-400'" x-text="money(s.outstanding)"></td>
                                <td><x-admin.badge expr="s.status" class-expr="s.status === 'active' ? 'border-emerald-400 bg-emerald-50 text-emerald-800' : 'border-slate-300 bg-slate-100 text-slate-500'" label-expr="s.status === 'active' ? 'Active' : 'Inactive'" /></td>
                                <td><x-pos.icon name="chevron-right" class="h-4 w-4 text-slate-300" /></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    <x-admin.toast />
    <div x-ref="overlayRoot"><x-purchases.overlays /></div>
</body>
</html>
