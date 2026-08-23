<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css'])
</head>
@php
    $employee = auth()->user()?->employee?->loadMissing('role', 'branch');
    $permissions = $employee?->effectivePermissions() ?? [];
    $initials = $employee
        ? collect(explode(' ', $employee->name))->filter()->map(fn ($word) => strtoupper($word[0]))->take(2)->implode('')
        : 'U';
@endphp
<body class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="dashboard" />

    <div class="adm-main">
        <x-admin.page-header title="Dashboard" subtitle="Ichapur Main Branch">
            <div class="flex items-center gap-2">
                <a href="{{ route('password.change') }}" class="flex h-8 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
                    <x-pos.icon name="lock" class="h-3.5 w-3.5" /> Password
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
                        Logout
                    </button>
                </form>
            </div>
        </x-admin.page-header>

        <div class="pos-infobar flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-4 pos-no-scrollbar">
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Signed In</span><span class="text-[13px] font-black text-slate-900">{{ $employee?->name ?? auth()->user()?->name }}</span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Role</span><span class="text-[13px] font-black text-slate-900">{{ $employee?->role?->name ?? 'Staff' }}</span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Branch</span><span class="text-[13px] font-black text-slate-900">{{ $employee?->branch?->name ?? 'Main Branch' }}</span></div>
        </div>

        <div class="grid gap-4 p-4 xl:grid-cols-[1fr_360px]">
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-500">Today's Sales</p>
                    <p class="pos-num mt-3 text-2xl font-black text-slate-900">₹48,320</p>
                    <p class="mt-1 text-[12px] font-semibold text-emerald-700">Across 186 orders</p>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-500">Orders Today</p>
                    <p class="pos-num mt-3 text-2xl font-black text-slate-900">186</p>
                    <p class="mt-1 text-[12px] font-semibold text-slate-500">14 reserved tables</p>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-500">Avg Order Value</p>
                    <p class="pos-num mt-3 text-2xl font-black text-slate-900">₹259</p>
                    <p class="mt-1 text-[12px] font-semibold text-slate-500">Stable today</p>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-500">Occupied Tables</p>
                    <p class="pos-num mt-3 text-2xl font-black text-slate-900">27 / 42</p>
                    <p class="mt-1 text-[12px] font-semibold text-rose-600">4 delayed</p>
                </div>
            </section>

            <aside class="rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-3">
                    <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-500">Logged In Profile</p>
                </div>
                <div class="p-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-full bg-brand-600 text-[14px] font-black text-white">{{ $initials }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-[15px] font-black text-slate-900">{{ $employee?->name ?? auth()->user()?->name }}</p>
                            <p class="text-[12px] font-semibold text-slate-500">{{ $employee?->employee_code ?? 'User' }} · {{ $employee?->role?->name ?? 'Staff' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-[12px]">
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2"><p class="font-black uppercase text-slate-400">Phone</p><p class="font-semibold text-slate-800">{{ $employee?->phone ?? '-' }}</p></div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2"><p class="font-black uppercase text-slate-400">Status</p><p class="font-semibold text-emerald-700">{{ ucfirst($employee?->status ?? 'active') }}</p></div>
                        <div class="col-span-2 rounded-md border border-slate-200 bg-slate-50 p-2"><p class="font-black uppercase text-slate-400">Email</p><p class="break-all font-semibold text-slate-800">{{ $employee?->email ?? auth()->user()?->email }}</p></div>
                    </div>
                    <p class="mt-4 text-[10.5px] font-black uppercase tracking-wide text-slate-500">Enabled Modules</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @forelse (array_keys($permissions) as $module)
                            <span class="rounded border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-700">{{ $module }}</span>
                        @empty
                            <span class="text-[12px] font-semibold text-slate-400">No module permissions assigned.</span>
                        @endforelse
                    </div>
                </div>
            </aside>

            <section class="rounded-md border border-slate-200 bg-white p-4 xl:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-500">Quick Access</p>
                        <h2 class="text-[16px] font-black text-slate-900">Allowed Work Areas</h2>
                    </div>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['pos', 'POS', 'POS'], ['orders', 'Tables', 'Orders'], ['kds', 'Kitchen', 'Kitchen'],
                        ['customers', 'Customers', 'Customers'], ['menu', 'Menu', 'Menu'], ['inventory', 'Inventory', 'Inventory'],
                        ['purchases', 'Purchases', 'Purchases'], ['expenses', 'Expenses', 'Expenses'], ['reports', 'Reports', 'Reports'],
                    ] as [$route, $label, $module])
                        @if (Route::has($route) && auth()->user()?->hasPermission($module, 'View'))
                            <a href="{{ route($route) }}" class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 text-[12px] font-bold text-slate-700 hover:border-slate-900 hover:bg-white">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</body>
</html>
