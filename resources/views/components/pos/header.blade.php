{{-- PosHeader: fixed 46/52px band for identity, order type, and terminal telemetry. --}}
<header class="pos-header z-30 flex items-center gap-3 border-b border-slate-800 bg-slate-900 px-3 text-slate-100">
    @php
        $appMenuGroups = [
            'Operations' => [
                ['dashboard', 'Dashboard'],
                ['pos', 'POS'],
                ['tables', 'Tables'],
                ['orders', 'Orders'],
                ['kds', 'Kitchen'],
                ['reservations', 'Reservations'],
            ],
            'Customers & Menu' => [
                ['customers', 'Customers'],
                ['menu', 'Menu'],
            ],
            'Stock & Finance' => [
                ['inventory', 'Inventory'],
                ['purchases', 'Purchases'],
                ['expenses', 'Expenses'],
            ],
            'Management' => [
                ['billing', 'Billing'],
                ['reports', 'Reports'],
                ['employees', 'Employees'],
                ['settings', 'Settings'],
            ],
        ];
        $routePermissions = \App\Http\Controllers\Auth\AuthenticatedSessionController::allowedRoutePermissions();
        $user = auth()->user();
    @endphp

    {{-- Identity --}}
    <a href="{{ route('app.start') }}" class="flex min-w-0 items-center gap-2 rounded px-1 py-1 hover:bg-slate-800">
        <span class="grid h-7 w-7 shrink-0 place-items-center rounded bg-brand-600 text-[11px] font-black tracking-tight text-white"
              x-text="venue.initials || 'RB'"></span>
        <span class="min-w-0 leading-none">
            <span class="block truncate text-[12.5px] font-bold text-white" x-text="venue.name || 'Royal Bengal Restaurant'"></span>
            <span class="mt-0.5 hidden truncate text-[10px] font-medium text-slate-400 xl:block"
                  x-text="[venue.branch || 'Main Branch', venue.terminal || 'POS Terminal'].filter(Boolean).join(' - ')"></span>
        </span>
    </a>

    <div class="relative shrink-0" @click.outside="appMenuOpen = false">
        <button type="button"
                @click="toggleAppMenu()"
                :aria-expanded="appMenuOpen.toString()"
                title="Open app menu"
                class="flex h-8 items-center gap-1.5 rounded-md border border-slate-700 px-2 text-[10.5px] font-black uppercase tracking-wide text-slate-300 hover:border-slate-600 hover:bg-slate-800 hover:text-white">
            <x-pos.icon name="layers" class="h-4 w-4" />
            <span class="hidden xl:inline">Menu</span>
        </button>

        <div x-show="appMenuOpen"
             x-transition.origin.top.left
             class="absolute left-0 top-10 z-50 w-64 overflow-hidden rounded-md border border-slate-700 bg-slate-950 shadow-2xl"
             style="display: none;">
            @foreach ($appMenuGroups as $group => $items)
                @php
                    $visibleItems = collect($items)->filter(function ($item) use ($routePermissions, $user) {
                        [$route] = $item;
                        $permission = $routePermissions[$route] ?? null;

                        return $permission && Route::has($route) && $user?->hasPermission($permission[0], $permission[1]);
                    });
                @endphp

                @continue($visibleItems->isEmpty())

                <div class="border-b border-slate-800 p-1.5 last:border-b-0">
                    <p class="px-2 py-1 text-[9px] font-black uppercase tracking-wide text-slate-500">{{ $group }}</p>
                    @foreach ($visibleItems as [$route, $label])
                        <a href="{{ route($route) }}"
                           @class([
                               'flex items-center justify-between rounded px-2.5 py-2 text-[12px] font-bold transition-colors',
                               'bg-brand-600/20 text-brand-100' => request()->routeIs($route),
                               'text-slate-200 hover:bg-slate-800 hover:text-white' => ! request()->routeIs($route),
                           ])>
                            <span>{{ $label }}</span>
                            @if (request()->routeIs($route))
                                <span class="rounded bg-brand-500/20 px-1.5 py-0.5 text-[9px] font-black uppercase text-brand-100">Open</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <button type="button"
            @click="toggleSidebar()"
            :aria-pressed="sidebarOpen.toString()"
            :title="sidebarOpen ? 'Hide menu sidebar' : 'Show menu sidebar'"
            class="grid h-8 w-8 shrink-0 place-items-center rounded-md border border-slate-700 text-slate-300 hover:border-slate-600 hover:bg-slate-800 hover:text-white">
        <x-pos.icon name="grid" class="h-4 w-4" />
    </button>

    {{-- OrderTypeSelector --}}
    <div class="mx-auto flex shrink-0 rounded-md bg-slate-800 p-0.5" role="tablist" aria-label="Order type">
        @foreach ([['dinein', 'Dine In', 'table'], ['takeaway', 'Takeaway', 'bag'], ['delivery', 'Delivery', 'scooter']] as [$key, $label, $icon])
            <button type="button" role="tab"
                    @click="orderType = '{{ $key }}'"
                    :aria-selected="orderType === '{{ $key }}'"
                    :class="orderType === '{{ $key }}'
                        ? 'bg-white text-slate-900 shadow-sm'
                        : 'text-slate-300 hover:text-white'"
                    class="flex items-center gap-1.5 rounded px-3 py-1.5 text-[11.5px] font-bold uppercase tracking-[0.05em]">
                <x-pos.icon name="{{ $icon }}" class="h-3.5 w-3.5" />
                <span>{{ $label }}</span>
            </button>
        @endforeach
    </div>

    {{-- Telemetry --}}
    <div class="flex shrink-0 items-center gap-2">

        {{-- KitchenStatusIndicator --}}
        <button type="button" @click="open('kitchen')"
                class="hidden items-center gap-1.5 rounded-md border border-slate-700 bg-slate-800/70 px-2 py-1 hover:border-slate-600 lg:flex"
                title="Kitchen load - click for ready items">
            <x-pos.icon name="chef" class="h-3.5 w-3.5 text-slate-400" />
            <span class="pos-num text-[10px] font-bold uppercase tracking-wide text-slate-300">
                New <span class="text-white" x-text="kitchen.new"></span>
            </span>
            <span class="pos-num text-[10px] font-bold uppercase tracking-wide text-slate-300">
                Prep <span class="text-white" x-text="kitchen.prep"></span>
            </span>
            <span class="pos-num rounded bg-emerald-500/15 px-1.5 text-[10px] font-bold uppercase tracking-wide text-emerald-300 ring-1 ring-emerald-500/40">
                Ready <span x-text="kitchen.ready"></span>
            </span>
        </button>

        {{-- Running orders --}}
        <button type="button" @click="open('running')"
                class="hidden items-center gap-1.5 rounded-md border border-slate-700 px-2 py-1.5 text-[10.5px] font-bold uppercase tracking-wide text-slate-300 hover:border-slate-600 hover:text-white xl:flex">
            <x-pos.icon name="receipt" class="h-3.5 w-3.5" />
            Running <span class="pos-num text-white" x-text="runningOrders.length"></span>
            <kbd class="rounded bg-slate-800 px-1 text-[9px] text-slate-400">F10</kbd>
        </button>

        {{-- Connectivity --}}
        <div class="hidden items-center gap-1 rounded-md border border-slate-700 px-2 py-1.5 md:flex"
             title="Terminal online - station printers reachable">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-300">Online</span>
        </div>

        {{-- Notifications --}}
        <button type="button" @click="open('kitchen')"
                class="relative grid h-8 w-8 place-items-center rounded-md text-slate-300 hover:bg-slate-800 hover:text-white"
                aria-label="Notifications">
            <x-pos.icon name="bell" class="h-4 w-4" />
            <span x-show="alerts.length"
                  class="pos-num absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-emerald-500 px-1 text-[9px] font-black text-slate-950"
                  x-text="alerts.length"></span>
        </button>

        <div class="h-6 w-px bg-slate-700"></div>

        {{-- Clock + shift --}}
        <div class="text-right leading-none">
            <p class="pos-num text-[13px] font-bold text-white" x-text="clock"></p>
            <p class="mt-0.5 text-[9.5px] font-bold uppercase tracking-[0.08em] text-emerald-400">
                Shift <span x-text="operator.shift"></span>
            </p>
        </div>

        {{-- Operator --}}
        <button type="button" @click="open('shortcuts')"
                class="flex items-center gap-2 rounded-md px-1.5 py-1 hover:bg-slate-800"
                title="Operator - keyboard shortcuts (?)">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-700 text-[11px] font-bold text-white"
                  x-text="operator.initials"></span>
            <span class="hidden text-left leading-none lg:block">
                <span class="block text-[11.5px] font-bold text-white" x-text="operator.name"></span>
                <span class="mt-0.5 block text-[9.5px] font-medium uppercase tracking-wide text-slate-400" x-text="operator.role"></span>
            </span>
            <x-pos.icon name="chevron-down" class="h-3.5 w-3.5 text-slate-500" />
        </button>
    </div>
</header>
