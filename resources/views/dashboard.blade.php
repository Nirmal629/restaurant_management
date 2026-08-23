<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard | Restaurant OS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <div class="min-h-screen">
            <div class="flex min-h-screen">
                <aside class="hidden w-72 flex-col border-r border-slate-200 bg-slate-900 text-slate-100 lg:flex">
                    <div class="flex items-center gap-3 border-b border-slate-800 px-5 py-5">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600/20 ring-1 ring-white/10">
                            <svg viewBox="0 0 24 24" class="h-6 w-6 text-brand-400" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5V8.8A2.8 2.8 0 0 1 6.8 6h10.4A2.8 2.8 0 0 1 20 8.8v1.7"/>
                                <path d="M5.5 10.5h13l-1.2 7.2a2.2 2.2 0 0 1-2.2 1.8H8.9a2.2 2.2 0 0 1-2.2-1.8L5.5 10.5Z"/>
                                <path d="M8 10.5V8.4A4 4 0 0 1 12 4.5a4 4 0 0 1 4 3.9v2.1"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Restaurant OS</p>
                            <h1 class="text-xl font-bold">DineFlow</h1>
                        </div>
                    </div>

                    <nav class="flex-1 space-y-2 px-4 py-5">
                        <div class="rounded-xl bg-brand-600/15 px-3 py-2 text-sm font-semibold text-brand-200">Dashboard</div>
                        <a href="{{ route('pos') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">POS</a>
                        <a href="{{ route('tables') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Tables</a>
                        <a href="#" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Orders</a>
                        <a href="{{ route('kds') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Kitchen</a>
                        <a href="{{ route('reservations') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Reservations</a>
                        <a href="{{ route('customers') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Customers</a>
                        <a href="{{ route('menu') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Menu</a>
                        <a href="{{ route('inventory') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Inventory</a>
                        <a href="{{ route('purchases') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Purchases</a>
                        <a href="{{ route('expenses') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Expenses</a>
                        <a href="{{ route('billing') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Billing</a>
                        <a href="{{ route('reports') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Reports</a>
                        <a href="{{ route('employees') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Employees</a>
                        <a href="{{ route('settings') }}" class="flex items-center rounded-xl px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Settings</a>
                    </nav>

                    <div class="border-t border-slate-800 p-4">
                        <div class="flex items-center gap-3 rounded-xl bg-slate-800/70 p-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">AR</div>
                            <div>
                                <p class="text-sm font-semibold text-white">Aisha Rahman</p>
                                <p class="text-xs text-slate-400">Owner</p>
                            </div>
                        </div>
                    </div>
                </aside>

                <main class="flex-1">
                    <header class="border-b border-slate-200 bg-white/90 backdrop-blur-sm">
                        <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Operations Overview</p>
                                <h2 class="mt-1 text-2xl font-bold text-slate-900">Dashboard</h2>
                            </div>

                            <div class="flex items-center gap-3">
                                <button class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Today</button>
                                <button class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">This Week</button>
                                <a href="{{ route('pos') }}" class="rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ New Order</a>
                            </div>
                        </div>
                    </header>

                    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
                        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Today’s Sales</p>
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">+12.4%</span>
                                </div>
                                <p class="mt-4 text-3xl font-bold text-slate-900">₹48,320</p>
                                <p class="mt-2 text-sm text-slate-500">Across 186 orders</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Orders Today</p>
                                    <span class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">+8</span>
                                </div>
                                <p class="mt-4 text-3xl font-bold text-slate-900">186</p>
                                <p class="mt-2 text-sm text-slate-500">14 reserved tables</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Avg Order Value</p>
                                    <span class="rounded-full bg-sky-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-sky-700">Stable</span>
                                </div>
                                <p class="mt-4 text-3xl font-bold text-slate-900">₹259</p>
                                <p class="mt-2 text-sm text-slate-500">From 186 completed orders</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Occupied Tables</p>
                                    <span class="rounded-full bg-red-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-red-700">4 delayed</span>
                                </div>
                                <p class="mt-4 text-3xl font-bold text-slate-900">27 / 42</p>
                                <p class="mt-2 text-sm text-slate-500">8 reserved / 7 available</p>
                            </div>
                        </section>

                        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="mb-6 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Sales</p>
                                        <h3 class="mt-1 text-xl font-bold text-slate-900">Hourly Sales</h3>
                                    </div>
                                    <button class="text-sm font-medium text-brand-600">View report</button>
                                </div>
                                <div class="flex h-64 items-end gap-3">
                                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                                        <div class="w-full rounded-t-xl bg-brand-500/85" style="height: 25%"></div>
                                        <span class="text-xs text-slate-500">12 PM</span>
                                    </div>
                                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                                        <div class="w-full rounded-t-xl bg-brand-500/85" style="height: 42%"></div>
                                        <span class="text-xs text-slate-500">1 PM</span>
                                    </div>
                                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                                        <div class="w-full rounded-t-xl bg-brand-500/85" style="height: 58%"></div>
                                        <span class="text-xs text-slate-500">2 PM</span>
                                    </div>
                                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                                        <div class="w-full rounded-t-xl bg-brand-500/85" style="height: 75%"></div>
                                        <span class="text-xs text-slate-500">3 PM</span>
                                    </div>
                                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                                        <div class="w-full rounded-t-xl bg-brand-500/85" style="height: 68%"></div>
                                        <span class="text-xs text-slate-500">4 PM</span>
                                    </div>
                                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                                        <div class="w-full rounded-t-xl bg-brand-500/85" style="height: 88%"></div>
                                        <span class="text-xs text-slate-500">5 PM</span>
                                    </div>
                                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                                        <div class="w-full rounded-t-xl bg-brand-500/85" style="height: 96%"></div>
                                        <span class="text-xs text-slate-500">6 PM</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="mb-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Kitchen</p>
                                            <h3 class="mt-1 text-xl font-bold text-slate-900">Live Status</h3>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                            <span class="text-sm text-slate-600">Active KOTs</span>
                                            <span class="text-sm font-bold text-slate-900">24</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                            <span class="text-sm text-slate-600">Avg prep time</span>
                                            <span class="text-sm font-bold text-slate-900">18 min</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl bg-red-50 px-3 py-2">
                                            <span class="text-sm text-red-700">Delayed orders</span>
                                            <span class="text-sm font-bold text-red-700">04</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="mb-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Financial</p>
                                            <h3 class="mt-1 text-xl font-bold text-slate-900">Gross Profit</h3>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                            <span class="text-sm text-slate-600">Food cost</span>
                                            <span class="text-sm font-bold text-slate-900">₹18,470</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                            <span class="text-sm text-slate-600">Expenses</span>
                                            <span class="text-sm font-bold text-slate-900">₹7,240</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2">
                                            <span class="text-sm text-emerald-700">Estimated net profit</span>
                                            <span class="text-sm font-bold text-emerald-700">₹22,610</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Operations</p>
                                        <h3 class="mt-1 text-xl font-bold text-slate-900">Upcoming Reservations</h3>
                                    </div>
                                    <button class="text-sm font-medium text-brand-600">View all</button>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">Table 12 - Family Dinner</p>
                                            <p class="text-sm text-slate-500">7:30 PM · 6 guests · Rahul</p>
                                        </div>
                                        <span class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Confirmed</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">Table 18 - Birthday Party</p>
                                            <p class="text-sm text-slate-500">8:00 PM · 8 guests · Priya</p>
                                        </div>
                                        <span class="rounded-full bg-sky-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-sky-700">Arrived</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">VIP Lounge - Business Meet</p>
                                            <p class="text-sm text-slate-500">8:30 PM · 4 guests · Arjun</p>
                                        </div>
                                        <span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Seated</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Inventory</p>
                                        <h3 class="mt-1 text-xl font-bold text-slate-900">Low Stock</h3>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-amber-800">Chicken</span>
                                            <span class="text-sm font-bold text-amber-800">8 kg</span>
                                        </div>
                                    </div>
                                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-amber-800">Rice</span>
                                            <span class="text-sm font-bold text-amber-800">12 kg</span>
                                        </div>
                                    </div>
                                    <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-red-800">Milk</span>
                                            <span class="text-sm font-bold text-red-800">Out</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
