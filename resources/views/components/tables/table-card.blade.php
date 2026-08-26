@props(['card' => 'c'])

{{--
    RestaurantTableCard — content is entirely status-driven; only what matters
    for that status renders, so no card is ever overloaded with dead fields.
--}}
<button type="button"
        @click="openCard({{ $card }})"
        @contextmenu="openContextMenu({{ $card }}, $event)"
        draggable="true"
        @dragstart="dragStart({{ $card }}.ids[0])"
        @dragover.prevent="dragOverTarget({{ $card }}.ids[0])"
        @dragend="dragEnd()"
        :class="[
            cardShapeClass({{ $card }}.shape),
            editLayout ? 'flr-editable' : '',
            dragId === {{ $card }}.ids[0] ? 'flr-dragging' : '',
            {{ $card }}.shape === 'rect' || {{ $card }}.kind === 'group' ? 'flr-span-2' : '',
            {{ $card }}.status === 'disabled' ? 'pos-hatch border-slate-300 bg-slate-100' :
            {{ $card }}.status === 'available' ? 'border-emerald-300 bg-emerald-50 hover:border-emerald-600' :
            {{ $card }}.status === 'occupied' ? 'border-sky-300 bg-white hover:border-sky-600' :
            {{ $card }}.status === 'reserved' ? 'border-violet-300 bg-violet-50 hover:border-violet-600' :
            {{ $card }}.status === 'billing' ? 'border-amber-400 bg-amber-50 hover:border-amber-600' :
            'border-slate-300 bg-slate-100 hover:border-slate-500',
            {{ $card }}.kind === 'group' ? 'ring-1 ring-inset ring-slate-900/10' : '',
        ]"
        class="flr-card group relative flex flex-col justify-between border-2 p-2 text-left">

    {{-- Group link glyph --}}
    <span x-show="{{ $card }}.kind === 'group'"
          class="absolute -top-1.5 left-2 rounded bg-slate-900 px-1 text-[8.5px] font-black uppercase tracking-wide text-white">Group</span>
    <div x-show="upcomingBookings({{ $card }}).length"
         x-cloak
         class="pointer-events-none absolute left-1/2 top-full z-40 mt-1 hidden w-64 -translate-x-1/2 rounded-md border border-slate-300 bg-white p-2 text-left shadow-xl group-hover:block group-focus-visible:block">
        <p class="mb-1.5 text-[10px] font-black uppercase tracking-wide text-slate-500">Upcoming Bookings</p>
        <template x-for="day in ['today', 'tomorrow']" :key="day">
            <div x-show="bookingsForDay({{ $card }}, day).length" class="mb-1.5 last:mb-0">
                <p class="mb-1 text-[9.5px] font-black uppercase tracking-wide text-slate-400" x-text="bookingDayLabel(day)"></p>
                <div class="space-y-1">
                    <template x-for="booking in bookingsForDay({{ $card }}, day)" :key="booking.id">
                        <div class="rounded border border-slate-200 bg-slate-50 px-2 py-1">
                            <div class="flex items-center gap-1.5">
                                <span class="pos-num text-[11px] font-black text-slate-900" x-text="bookingTimeLabel(booking.time)"></span>
                                <span class="rounded bg-violet-100 px-1 text-[8.5px] font-black uppercase tracking-wide text-violet-700" x-text="bookingStatusLabel(booking.status)"></span>
                            </div>
                            <p class="truncate text-[10.5px] font-bold text-slate-700" x-text="booking.customer"></p>
                            <p class="pos-num text-[9.5px] font-semibold text-slate-500" x-text="booking.guests + ' guests' + (booking.phone ? ' - ' + booking.phone : '')"></p>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Row 1: id · seats --}}
    <div class="flex items-start justify-between">
        <span class="pos-num truncate text-[15px] font-black leading-none tracking-tight text-slate-900" x-text="{{ $card }}.label"></span>
        <span class="pos-num flex shrink-0 items-center gap-0.5 text-[10px] font-bold text-slate-500">
            <x-pos.icon name="users" class="h-3 w-3" />
            <template x-if="{{ $card }}.status === 'occupied' || {{ $card }}.status === 'billing'">
                <span x-text="({{ $card }}.guests || 0) + '/' + {{ $card }}.seats"></span>
            </template>
            <template x-if="!({{ $card }}.status === 'occupied' || {{ $card }}.status === 'billing')">
                <span x-text="{{ $card }}.seats"></span>
            </template>
        </span>
    </div>

    {{-- Row 2: status-specific detail --}}
    <div class="min-w-0 flex-1">
        {{-- AVAILABLE --}}
        <template x-if="{{ $card }}.status === 'available'">
            <p class="mt-1 text-[10.5px] font-semibold text-emerald-700">Ready to seat</p>
        </template>

        {{-- OCCUPIED --}}
        <template x-if="{{ $card }}.status === 'occupied'">
            <div class="mt-0.5 space-y-0.5">
                <p class="truncate text-[11px] font-bold text-slate-700" x-text="{{ $card }}.waiter"></p>
                <p class="pos-num text-[13px] font-black text-slate-900" x-text="money({{ $card }}.amount)"></p>
            </div>
        </template>

        {{-- RESERVED --}}
        <template x-if="{{ $card }}.status === 'reserved'">
            <div class="mt-0.5 space-y-0.5">
                <p class="pos-num text-[12px] font-black text-violet-800" x-text="reservationTime({{ $card }})"></p>
                <p class="truncate text-[10.5px] font-semibold text-violet-700" x-text="reservationGuestLine({{ $card }})"></p>
            </div>
        </template>

        {{-- BILLING --}}
        <template x-if="{{ $card }}.status === 'billing'">
            <div class="mt-0.5 space-y-0.5">
                <p class="pos-num text-[14px] font-black text-amber-800" x-text="money({{ $card }}.amount)"></p>
                <p class="text-[10px] font-bold uppercase tracking-wide text-amber-600">Payment pending</p>
            </div>
        </template>

        {{-- CLEANING --}}
        <template x-if="{{ $card }}.status === 'cleaning'">
            <p class="pos-num mt-1 text-[10.5px] font-semibold text-slate-500" x-text="cleaningLabel({{ $card }})"></p>
        </template>

        {{-- DISABLED --}}
        <template x-if="{{ $card }}.status === 'disabled'">
            <p class="mt-1 truncate text-[10px] font-semibold text-slate-500" x-text="{{ $card }}.note || 'Not in service'"></p>
        </template>
    </div>

    {{-- Row 3: status badge + secondary indicators --}}
    <div class="flex items-center gap-1">
        <x-tables.status-badge expr="{{ $card }}.status" size="sm" />

        <span class="flex-1"></span>

        {{-- Duration, flagged past threshold --}}
        <span x-show="{{ $card }}.status === 'occupied'"
              class="pos-num flex items-center gap-0.5 text-[10px] font-bold"
              :class="isLong({{ $card }}) ? 'text-rose-600' : 'text-slate-400'">
            <x-pos.icon name="clock" class="h-3 w-3" />
            <span x-text="{{ $card }}.since + 'm'"></span>
        </span>

        {{-- Kitchen ready indicator — opens the ready-items popover --}}
        <button type="button" x-show="{{ $card }}.kitchen?.ready > 0"
                @click.stop="activeTableId = {{ $card }}.id; activeGroupId = {{ $card }}.groupId || null; open('ready')"
                class="pos-num flex items-center gap-0.5 rounded border border-emerald-400 bg-emerald-100 px-1 text-[9.5px] font-bold text-emerald-800">
            <span x-text="{{ $card }}.kitchen.ready"></span> READY
        </button>
    </div>
</button>

