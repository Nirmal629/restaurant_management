<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/orders.js'])
    <script>
        window.ordersModule = @json($ordersPayload);
        window.ordersRoutes = {
            data: @json(route('orders.data')),
            orders: @json(url('/orders')),
            items: @json(url('/orders/items')),
            pos: @json(route('pos')),
            kds: @json(route('kds')),
            billing: @json(route('billing')),
        };
        window.realtimeVersionsUrl = @json(route('realtime.versions'));
        window.realtimeStreamUrl = @json(route('realtime.stream'));
    </script>
</head>
<body x-data="ordersApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">
    <x-shell.sidebar active="orders" />

    <div class="adm-main">
        <x-admin.page-header title="Orders" subtitle="Live order control from POS to kitchen to billing">
            <a href="{{ route('pos') }}" class="flex h-8 items-center rounded-md bg-slate-900 px-3 text-[11px] font-black uppercase tracking-wide text-white">Open POS</a>
            <a href="{{ route('kds') }}" class="flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-[11px] font-bold text-slate-700">Kitchen</a>
        </x-admin.page-header>

        <div class="grid grid-cols-4 gap-2 border-b border-slate-200 bg-white px-4 py-3">
            <template x-for="[key,label] in [['active','Active'],['kitchen','In Kitchen'],['billing','Billing'],['paidToday','Paid Today']]" :key="key">
                <div class="rounded-md border border-slate-200 bg-slate-50 p-2">
                    <p class="text-[9.5px] font-black uppercase tracking-wide text-slate-400" x-text="label"></p>
                    <p class="pos-num text-[18px] font-black text-slate-900" x-text="summary[key] || 0"></p>
                </div>
            </template>
        </div>

        <div class="pos-workspace">
            <aside class="w-80 shrink-0 border-r border-slate-200 bg-white">
                <div class="border-b border-slate-200 p-2">
                    <input x-model="query" placeholder="Search order / table / customer" class="h-9 w-full rounded-md border border-slate-300 px-3 text-[12px] font-semibold focus:border-slate-900 focus:outline-none">
                    <div class="mt-2 grid grid-cols-5 gap-1">
                        <template x-for="[key,label] in [['active','Active'],['kitchen','Kitchen'],['billing','Bill'],['history','History'],['all','All']]" :key="key">
                            <button type="button" @click="filter = key" :class="filter === key ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600'" class="h-7 rounded text-[10px] font-black uppercase" x-text="label"></button>
                        </template>
                    </div>
                </div>

                <div class="pos-scroll space-y-1.5 p-2">
                    <template x-for="order in filteredOrders" :key="order.id">
                        <button type="button" @click="select(order)" :class="activeOrder?.id === order.id ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-800 hover:border-slate-400'" class="w-full rounded-md border p-2 text-left">
                            <div class="flex items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="pos-num truncate text-[12.5px] font-black" x-text="order.code"></p>
                                    <p class="mt-0.5 truncate text-[10.5px] font-semibold opacity-70" x-text="order.table + ' · ' + order.customer"></p>
                                </div>
                                <span :class="statusClass(order.status)" class="rounded border px-1.5 py-px text-[8.5px] font-black uppercase" x-text="orderStatusLabel(order.status)"></span>
                            </div>
                            <div class="mt-2 grid grid-cols-3 gap-1 text-[10px] font-bold">
                                <span class="rounded bg-slate-100 px-1.5 py-1 text-slate-500" :class="activeOrder?.id === order.id && 'bg-white/10 text-slate-200'" x-text="order.itemCount + ' items'"></span>
                                <span class="pos-num rounded bg-slate-100 px-1.5 py-1 text-slate-500" :class="activeOrder?.id === order.id && 'bg-white/10 text-slate-200'" x-text="money(order.total)"></span>
                                <span class="rounded bg-slate-100 px-1.5 py-1 text-slate-500" :class="activeOrder?.id === order.id && 'bg-white/10 text-slate-200'" x-text="order.startedMinutesAgo + 'm'"></span>
                            </div>
                        </button>
                    </template>
                    <x-admin.empty-state icon="receipt" title="No orders match this view" x-show="!filteredOrders.length" />
                </div>
            </aside>

            <main class="flex min-w-0 flex-1 flex-col overflow-hidden" x-show="activeOrder">
                <div class="pos-dock flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="pos-num text-[16px] font-black text-slate-900" x-text="activeOrder.code"></p>
                        <p class="text-[11px] font-semibold text-slate-500" x-text="activeOrder.type + ' · ' + activeOrder.table + ' · Waiter ' + activeOrder.waiter"></p>
                    </div>
                    <span :class="statusClass(activeOrder.status)" class="rounded border px-2 py-1 text-[10px] font-black uppercase" x-text="orderStatusLabel(activeOrder.status)"></span>
                    <a :href="billingUrl(activeOrder)" class="h-9 rounded-md bg-slate-900 px-3 py-2 text-[11px] font-black uppercase tracking-wide text-white">Billing</a>
                </div>

                <div class="grid grid-cols-4 gap-2 border-b border-slate-200 bg-slate-50 p-3">
                    <div class="rounded-md border border-slate-200 bg-white p-2"><p class="text-[9px] font-black uppercase text-slate-400">Total</p><p class="pos-num text-[16px] font-black" x-text="money(activeOrder.total)"></p></div>
                    <div class="rounded-md border border-slate-200 bg-white p-2"><p class="text-[9px] font-black uppercase text-slate-400">Paid</p><p class="pos-num text-[16px] font-black text-emerald-700" x-text="money(activeOrder.paid)"></p></div>
                    <div class="rounded-md border border-slate-200 bg-white p-2"><p class="text-[9px] font-black uppercase text-slate-400">Due</p><p class="pos-num text-[16px] font-black text-rose-600" x-text="money(activeOrder.due)"></p></div>
                    <div class="rounded-md border border-slate-200 bg-white p-2"><p class="text-[9px] font-black uppercase text-slate-400">Kitchen Open</p><p class="pos-num text-[16px] font-black" x-text="activeOrder.kitchenOpen"></p></div>
                </div>

                <div class="pos-scroll p-3">
                    <div class="adm-table-wrap bg-white">
                        <table class="adm-table">
                            <thead><tr><th>Item</th><th>Station</th><th>KOT</th><th>Qty</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                <template x-for="item in activeOrder.items" :key="item.id">
                                    <tr>
                                        <td><p class="font-bold text-slate-900" x-text="item.name"></p><p class="text-[10.5px] text-slate-400" x-text="item.note || ''"></p></td>
                                        <td x-text="item.station"></td>
                                        <td class="pos-num" x-text="'#' + item.kotRound"></td>
                                        <td class="pos-num" x-text="item.qty"></td>
                                        <td><span :class="statusClass(item.status)" class="rounded border px-1.5 py-px text-[9px] font-black uppercase" x-text="itemStatusLabel(item.status)"></span></td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button type="button" @click="setItemStatus(item, 'preparing')" x-show="['sent','accepted'].includes(item.status)" class="h-7 rounded border border-slate-300 px-2 text-[10px] font-bold">Prep</button>
                                                <button type="button" @click="setItemStatus(item, 'ready')" x-show="item.status === 'preparing'" class="h-7 rounded border border-emerald-300 px-2 text-[10px] font-bold text-emerald-700">Ready</button>
                                                <button type="button" @click="setItemStatus(item, 'served')" x-show="item.status === 'ready'" class="h-7 rounded bg-slate-900 px-2 text-[10px] font-bold text-white">Serve</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pos-dock flex gap-2 border-t border-slate-200 bg-white px-4 py-2.5">
                    <button type="button" @click="setOrderStatus(activeOrder, 'billing')" :disabled="activeOrder.kitchenOpen > 0 || activeOrder.status !== 'open'" class="h-9 rounded-md bg-amber-500 px-3 text-[11px] font-black uppercase text-white disabled:opacity-40">Send To Billing</button>
                    <button type="button" @click="setOrderStatus(activeOrder, 'cancelled')" x-show="!['paid','completed','cancelled'].includes(activeOrder.status)" class="h-9 rounded-md border border-rose-300 px-3 text-[11px] font-bold text-rose-600">Cancel Order</button>
                </div>
            </main>
        </div>
    </div>

    <x-admin.toast />
</body>
</html>
