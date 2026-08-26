{{-- PageHeader — compact, matches PosHeader's density and dark chrome. --}}
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

    <a href="{{ route('app.start') }}" class="flex shrink-0 items-center gap-2 rounded px-1 py-1 hover:bg-slate-800">
        <span class="grid h-7 w-7 place-items-center rounded bg-brand-600 text-[11px] font-black text-white">RB</span>
        <span class="hidden leading-none sm:block">
            <span class="block text-[13px] font-bold text-white">Floor &amp; Tables</span>
            <span class="mt-0.5 block text-[10px] font-medium text-slate-400" x-text="venue.branch"></span>
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

    <div class="h-6 w-px shrink-0 bg-slate-700"></div>

    {{-- Shift + clock --}}
    <div class="flex shrink-0 items-center gap-2">
        <span class="hidden items-center gap-1 rounded-md border border-slate-700 px-2 py-1.5 text-[9.5px] font-bold uppercase tracking-wide text-emerald-400 md:flex">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            Shift <span x-text="operator.shift"></span>
        </span>
        <span class="pos-num hidden text-[13px] font-bold text-white lg:block" x-text="clock"></span>
    </div>

    <span class="flex-1"></span>

    {{-- Primary actions --}}
    <div class="flex shrink-0 items-center gap-1.5">
        <button type="button" @click="openFindTable()"
                class="flex h-8 items-center gap-1.5 rounded-md border border-slate-700 px-2.5 text-[11.5px] font-bold text-slate-200 hover:border-slate-500">
            <x-pos.icon name="search" class="h-3.5 w-3.5" /> Find Table
        </button>

        <button type="button" @click="openAddTable()"
                class="flex h-8 items-center gap-1.5 rounded-md border border-slate-700 px-2.5 text-[11.5px] font-bold text-slate-200 hover:border-slate-500">
            <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.2" /> Add Table
        </button>

        <button type="button" @click="openAddFloor()"
                class="hidden h-8 items-center gap-1.5 rounded-md border border-slate-700 px-2.5 text-[11.5px] font-bold text-slate-200 hover:border-slate-500 lg:flex">
            <x-pos.icon name="layers" class="h-3.5 w-3.5" /> Add Floor
        </button>

        <button type="button" @click="toggleEditLayout()"
                :class="editLayout ? 'border-amber-400 bg-amber-400 text-slate-950' : 'border-slate-700 text-slate-200 hover:border-slate-500'"
                class="flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-[11.5px] font-bold">
            <x-pos.icon name="grab" class="h-3.5 w-3.5" />
            <span x-text="editLayout ? 'Exit Layout' : 'Edit Layout'"></span>
        </button>

        {{-- More ▾ --}}
        <div class="relative" x-data="{ moreOpen: false }" @click.outside="moreOpen = false">
            <button type="button" @click="moreOpen = !moreOpen"
                    :class="moreOpen ? 'border-slate-500 bg-slate-800' : 'border-slate-700'"
                    class="flex h-8 items-center gap-1 rounded-md border px-2 text-slate-200 hover:border-slate-500">
                <x-pos.icon name="dots" class="h-3.5 w-3.5" stroke="2.4" />
            </button>
            <div x-show="moreOpen" x-cloak x-transition.origin.top.right
                 class="absolute right-0 top-9 z-30 w-56 overflow-hidden rounded-md border border-slate-700 bg-slate-900 py-1 shadow-2xl">
                <button type="button" @click="moreOpen = false; toggleEditLayout()" class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12px] font-semibold text-slate-200 hover:bg-slate-800">Edit floor layout</button>
                <button type="button" @click="moreOpen = false; notify('Sections are managed inside Edit Layout mode')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12px] font-semibold text-slate-200 hover:bg-slate-800">Manage sections</button>
                <button type="button" @click="moreOpen = false; notify('QR codes queued for the counter printer')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12px] font-semibold text-slate-200 hover:bg-slate-800">Print all QR codes</button>
                <button type="button" @click="moreOpen = false; notify('Table settings live in Settings → Floor & Tables')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12px] font-semibold text-slate-200 hover:bg-slate-800">Table settings</button>
            </div>
        </div>

        <a href="{{ route('pos') }}"
           class="flex h-8 items-center gap-1.5 rounded-md bg-emerald-600 px-2.5 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">
            <x-pos.icon name="cash" class="h-3.5 w-3.5" /> Open POS
        </a>
    </div>
</header>
