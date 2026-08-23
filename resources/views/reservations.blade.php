<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reservations · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/reservations.js'])
    <script>
        window.reservationsModule = @json($reservationsPayload);
        window.reservationsRoutes = {
            data: @json(route('reservations.data')),
            store: @json(route('reservations.store')),
            base: @json(url('/reservations')),
            pos: @json(route('pos')),
        };
        window.realtimeStreamUrl = @json(route('realtime.stream'));
    </script>
</head>
<body x-data="reservationsApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="reservations" />

    <div class="adm-main">
        <x-admin.page-header title="Reservations" subtitle="Ichapur Main Branch">
            <button type="button" @click="openFindTable()" class="flex h-8 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 text-[11.5px] font-bold text-slate-700 hover:border-slate-900">
                <x-pos.icon name="search" class="h-3.5 w-3.5" /> Find Table
            </button>
            <button type="button" @click="openCreate()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
                <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> New Reservation
            </button>
        </x-admin.page-header>

        {{-- Summary metrics --}}
        <div class="pos-infobar flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-4 pos-no-scrollbar">
            @foreach ([['today','Today','slate'],['confirmed','Confirmed','sky'],['arrived','Arrived','amber'],['seated','Seated','emerald'],['noShow','No Show','rose']] as [$key,$label,$tone])
                <div class="flex h-8 items-center gap-1.5 rounded-md px-2">
                    <span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">{{ $label }}</span>
                    <span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.{{ $key }}"></span>
                </div>
            @endforeach
        </div>

        <x-admin.tabs :tabs="['today' => 'Today', 'calendar' => 'Calendar', 'list' => 'List']" active="view" />

        {{-- ============================== TODAY ============================== --}}
        <template x-if="view === 'today'">
            <div class="pos-scroll bg-slate-100 p-3">
                <div class="grid gap-2.5" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <template x-for="r in todaysReservations" :key="r.id"><div><x-reservations.card /></div></template>
                </div>
                <x-admin.empty-state icon="calendar" title="No reservations today" hint="New bookings for today will appear here." x-show="!todaysReservations.length" />
            </div>
        </template>

        {{-- ============================== CALENDAR ============================== --}}
        <template x-if="view === 'calendar'">
            <div class="pos-scroll bg-slate-100 p-3">
                <div class="mb-3 flex items-center gap-2">
                    <div class="flex rounded-md border border-slate-300 bg-white p-0.5">
                        @foreach (['day' => 'Day', 'week' => 'Week', 'month' => 'Month'] as $k => $l)
                            <button type="button" @click="calMode = '{{ $k }}'" :class="calMode === '{{ $k }}' ? 'bg-slate-900 text-white' : 'text-slate-600'" class="rounded px-3 py-1.5 text-[11.5px] font-bold">{{ $l }}</button>
                        @endforeach
                    </div>
                    <template x-if="calMode === 'month'">
                        <div class="flex items-center gap-1">
                            <button type="button" @click="shiftMonth(-1)" class="grid h-7 w-7 place-items-center rounded border border-slate-300 hover:border-slate-900"><x-pos.icon name="chevron-left" class="h-3.5 w-3.5" /></button>
                            <span class="min-w-[130px] text-center text-[12.5px] font-bold text-slate-800" x-text="monthLabel"></span>
                            <button type="button" @click="shiftMonth(1)" class="grid h-7 w-7 place-items-center rounded border border-slate-300 hover:border-slate-900"><x-pos.icon name="chevron-right" class="h-3.5 w-3.5" /></button>
                        </div>
                    </template>
                </div>

                {{-- Month grid --}}
                <template x-if="calMode === 'month'">
                    <div>
                        <div class="grid grid-cols-7 gap-1.5 text-center text-[9.5px] font-black uppercase tracking-wide text-slate-400">
                            <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="d"><span x-text="d"></span></template>
                        </div>
                        <div class="mt-1.5 grid grid-cols-7 gap-1.5">
                            <template x-for="(cell, i) in monthCells" :key="i">
                                <button type="button" @click="cell && selectDay(cell.iso)" :disabled="!cell"
                                        :class="cell ? (cell.iso === todayIso ? 'border-slate-900 bg-white' : 'border-slate-200 bg-white hover:border-slate-400') : 'border-transparent'"
                                        class="flex h-16 flex-col items-start justify-between rounded-md border p-1.5 text-left">
                                    <span x-show="cell" class="pos-num text-[11px] font-bold text-slate-600" x-text="cellDay(cell)"></span>
                                    <span x-show="cell && cell.count" class="pos-num self-end rounded bg-slate-900 px-1 text-[9px] font-bold text-white" x-text="cellCount(cell)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Week strip --}}
                <template x-if="calMode === 'week'">
                    <div class="grid gap-2" style="grid-template-columns: repeat(7, minmax(150px, 1fr));">
                        <template x-for="d in weekDays" :key="d.iso">
                            <div class="rounded-md border border-slate-200 bg-white p-2">
                                <button type="button" @click="selectDay(d.iso)" class="mb-1.5 w-full rounded px-1 py-0.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-600 hover:bg-slate-50" x-text="d.label"></button>
                                <div class="space-y-1">
                                    <template x-for="r in d.items" :key="r.id">
                                        <button type="button" @click="openDetail(r)" class="block w-full truncate rounded bg-slate-100 px-1.5 py-1 text-left text-[10.5px] font-semibold text-slate-700 hover:bg-slate-200">
                                            <span x-text="timeLabel(r.time) + ' · ' + r.customer"></span>
                                        </button>
                                    </template>
                                    <p x-show="!d.items.length" class="text-[10px] text-slate-300">—</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Day view --}}
                <template x-if="calMode === 'day'">
                    <div>
                        <p class="mb-2 text-[12.5px] font-bold text-slate-700" x-text="prettyDate(calSelected)"></p>
                        <div class="grid gap-2.5" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                            <template x-for="r in dayReservations" :key="r.id"><div><x-reservations.card r="r" /></div></template>
                        </div>
                        <x-admin.empty-state icon="calendar" title="No reservations this day" x-show="!dayReservations.length" />
                    </div>
                </template>
            </div>
        </template>

        {{-- ============================== LIST ============================== --}}
        <template x-if="view === 'list'">
            <div class="contents">
                <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <div class="relative min-w-[190px] max-w-xs flex-1">
                        <x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input x-model="query" @input="page = 1" placeholder="Search name / phone / RES number…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                    </div>
                    <select x-model="statusFilter" @change="page = 1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                        <option value="all">All Statuses</option>
                        <template x-for="s in ['pending','confirmed','arrived','seated','completed','cancelled','no_show']" :key="s"><option :value="s" x-text="statusLabel(s)"></option></template>
                    </select>
                    <select x-model="sourceFilter" @change="page = 1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none">
                        <option value="all">All Sources</option>
                        <template x-for="s in sources" :key="s"><option x-text="s"></option></template>
                    </select>
                    <input x-model="dateFrom" @change="page = 1" type="date" class="pos-num h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                    <span class="text-[11px] text-slate-400">to</span>
                    <input x-model="dateTo" @change="page = 1" type="date" class="pos-num h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium focus:border-slate-900 focus:outline-none" />
                    <button type="button" x-show="query || statusFilter !== 'all' || sourceFilter !== 'all' || dateFrom || dateTo" @click="clearFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900">
                        <x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset
                    </button>
                </div>

                <div class="adm-table-wrap bg-white">
                    <template x-if="loading"><x-admin.skeleton-rows :cols="7" /></template>
                    <table class="adm-table" x-show="!loading">
                        <thead>
                            <tr>
                                <th>Reservation</th><th>Customer</th><th>Phone</th><th>Date &amp; Time</th><th>Guests</th><th>Table</th><th>Source</th><th>Status</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="r in pagedList" :key="r.id">
                                <tr class="adm-row-clickable" @click="openDetail(r)">
                                    <td class="pos-num font-bold text-slate-900" x-text="r.id"></td>
                                    <td class="font-semibold text-slate-800" x-text="r.customer"></td>
                                    <td class="pos-num text-slate-500" x-text="r.phone"></td>
                                    <td class="pos-num text-slate-600" x-text="prettyDate(r.date) + ' · ' + timeLabel(r.time)"></td>
                                    <td class="pos-num text-slate-600" x-text="r.guests"></td>
                                    <td class="pos-num text-slate-600" x-text="r.table || '—'"></td>
                                    <td class="text-slate-500" x-text="r.source"></td>
                                    <td><x-admin.badge expr="r.status" /></td>
                                    <td @click.stop>
                                        <x-admin.action-menu id-expr="r.id">
                                            <button type="button" @click="openDetail(r)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Detail</button>
                                            <button type="button" x-show="!['seated','completed','cancelled','no_show'].includes(r.status)" @click="openEdit(r)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                            <button type="button" x-show="!['seated','completed','cancelled','no_show'].includes(r.status)" @click="openCancel(r)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Cancel</button>
                                        </x-admin.action-menu>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <x-admin.empty-state icon="calendar" title="No reservations match this filter" x-show="!loading && !pagedList.length" />
                </div>
                <x-admin.pagination total="filteredList.length" />
            </div>
        </template>
    </div>

    <x-admin.toast />
    <div x-ref="overlayRoot"><x-reservations.overlays /></div>
</body>
</html>
