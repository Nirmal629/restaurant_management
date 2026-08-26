@props(['active'])

{{--
    Shared app sidebar — same nav Dashboard already uses, reorganized into the
    four labelled groups every admin module now follows. `active` takes a
    route name so the current section is obviously highlighted.
--}}
@php
    $groups = [
        'OPERATIONS' => [
            ['dashboard', 'Dashboard'],
            ['pos', 'POS'],
            ['tables', 'Tables'],
            ['orders', 'Orders'],
            ['kds', 'Kitchen'],
            ['reservations', 'Reservations'],
        ],
        'CUSTOMERS & MENU' => [
            ['customers', 'Customers'],
            ['menu', 'Menu'],
        ],
        'STOCK & FINANCE' => [
            ['inventory', 'Inventory'],
            ['purchases', 'Purchases'],
            ['expenses', 'Expenses'],
        ],
        'MANAGEMENT' => [
            ['billing', 'Billing'],
            ['reports', 'Reports'],
            ['employees', 'Employees'],
            ['settings', 'Settings'],
        ],
    ];
    $routeModules = [
        'dashboard' => 'Dashboard',
        'pos' => 'POS',
        'tables' => 'Orders',
        'orders' => 'Orders',
        'kds' => 'Kitchen',
        'reservations' => 'Orders',
        'customers' => 'Customers',
        'menu' => 'Menu',
        'inventory' => 'Inventory',
        'purchases' => 'Purchases',
        'expenses' => 'Expenses',
        'billing' => 'Billing',
        'reports' => 'Reports',
        'employees' => 'Employees',
        'settings' => 'Settings',
    ];
    $user = auth()->user();
@endphp

<aside class="adm-sidebar border-r border-slate-800 bg-slate-900 text-slate-100">
    <a href="{{ route('app.start') }}" class="flex shrink-0 items-center gap-3 border-b border-slate-800 px-4 py-4">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-[12px] font-black text-white">RB</span>
        <span class="min-w-0 leading-tight max-[1279px]:hidden">
            <span class="block truncate text-[13px] font-bold text-white">Royal Bengal</span>
            <span class="block truncate text-[10px] font-medium text-slate-400">Ichapur Main Branch</span>
        </span>
    </a>

    <nav class="pos-scroll px-3 py-3">
        @foreach ($groups as $label => $items)
            <p class="mb-1.5 mt-3 px-2 text-[9.5px] font-black uppercase tracking-[0.1em] text-slate-500 first:mt-0 max-[1279px]:hidden">{{ $label }}</p>
            <div class="mb-1 space-y-0.5">
                @foreach ($items as [$route, $label2])
                    @continue(isset($routeModules[$route]) && ! $user?->hasPermission($routeModules[$route], 'View'))
                    <a href="{{ Route::has($route) ? route($route) : '#' }}"
                       @class([
                           'flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[12.5px] font-semibold transition-colors max-[1279px]:justify-center',
                           'bg-brand-600/20 text-brand-200' => $active === $route,
                           'text-slate-300 hover:bg-slate-800 hover:text-white' => $active !== $route,
                       ])>
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $active === $route ? 'bg-brand-400' : 'bg-slate-700' }} max-[1279px]:hidden"></span>
                        <span class="truncate max-[1279px]:hidden">{{ $label2 }}</span>
                        <span class="hidden text-[10px] font-black uppercase max-[1279px]:block">{{ substr($label2, 0, 2) }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    @php
        $employee = auth()->user()?->employee;
        $initials = $employee ? collect(explode(' ', $employee->name))->map(fn ($w) => strtoupper($w[0]))->take(2)->implode('') : 'U';
    @endphp
    <div class="shrink-0 border-t border-slate-800 p-3">
        <div class="flex items-center gap-2.5 rounded-lg bg-slate-800/70 p-2 max-[1279px]:justify-center">
            <a href="{{ route('profile.edit') }}" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-600 text-[11px] font-bold text-white ring-0 transition hover:ring-2 hover:ring-brand-300" title="View profile">{{ $initials }}</a>
            <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1 leading-tight max-[1279px]:hidden" title="View profile">
                <span class="block truncate text-[12px] font-semibold text-white">{{ $employee->name ?? auth()->user()?->name }}</span>
                <span class="block truncate text-[10px] text-slate-400">{{ $employee?->role?->name ?? 'Staff' }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="max-[1279px]:hidden">
                @csrf
                <button type="submit" class="grid h-7 w-7 shrink-0 place-items-center rounded-md text-slate-400 hover:bg-slate-700 hover:text-white" title="Log out">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3M15 8l4 4-4 4M19 12H9"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
