@props(['r' => 'r'])

{{-- Reservation card — used by Today, Day and Week views. Status drives which quick actions show. --}}
<div class="cursor-pointer rounded-md border-2 bg-white p-2.5 hover:shadow-sm"
     @click="openDetail({{ $r }})"
     :class="{
        pending: 'border-slate-300', confirmed: 'border-sky-300', arrived: 'border-amber-400',
        seated: 'border-emerald-400', completed: 'border-slate-200', cancelled: 'border-rose-200 opacity-60', no_show: 'border-rose-300 opacity-70',
      }[{{ $r }}.status]">

    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="pos-num text-[13.5px] font-black leading-tight text-slate-900" x-text="timeLabel({{ $r }}.time)"></p>
            <p class="truncate text-[12.5px] font-bold text-slate-800" x-text="{{ $r }}.customer"></p>
        </div>
        <x-admin.badge :expr="$r . '.status'" />
    </div>

    <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10.5px] font-semibold text-slate-500">
        <span class="pos-num flex items-center gap-1"><x-pos.icon name="users" class="h-3 w-3" /><span x-text="{{ $r }}.guests + ' guests'"></span></span>
        <span x-show="{{ $r }}.table" class="pos-num flex items-center gap-1"><x-pos.icon name="table" class="h-3 w-3" /><span x-text="{{ $r }}.table"></span></span>
        <span x-show="!{{ $r }}.table" class="text-amber-600">No table assigned</span>
        <span x-text="floorLabel({{ $r }}.floor)"></span>
    </div>

    <p x-show="{{ $r }}.occasion !== 'None'" class="mt-1 text-[10.5px] font-semibold text-violet-700" x-text="{{ $r }}.occasion"></p>
    <p x-show="{{ $r }}.request" class="mt-0.5 truncate text-[10.5px] italic text-slate-500" x-text="{{ $r }}.request"></p>

    <div class="mt-2 flex flex-wrap gap-1.5" @click.stop>
        <template x-if="{{ $r }}.status === 'pending'">
            <button type="button" @click="confirmReservation({{ $r }})" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="rounded-md bg-slate-900 px-2.5 py-1.5 text-[10.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-wait disabled:bg-slate-300">Confirm</button>
        </template>
        <template x-if="{{ $r }}.status === 'confirmed'">
            <button type="button" @click="markArrived({{ $r }})" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="rounded-md bg-amber-500 px-2.5 py-1.5 text-[10.5px] font-bold uppercase tracking-wide text-slate-950 hover:bg-amber-400 disabled:cursor-wait disabled:bg-slate-300 disabled:text-white">Mark Arrived</button>
        </template>
        <template x-if="{{ $r }}.status === 'arrived'">
            <button type="button" @click="openSeat({{ $r }})" class="rounded-md bg-emerald-600 px-2.5 py-1.5 text-[10.5px] font-bold uppercase tracking-wide text-white hover:bg-emerald-500">Seat Customer</button>
        </template>
        <template x-if="{{ $r }}.status === 'seated'">
            <a href="{{ route('pos') }}" class="rounded-md bg-emerald-600 px-2.5 py-1.5 text-[10.5px] font-bold uppercase tracking-wide text-white hover:bg-emerald-500">Open POS</a>
        </template>
        <template x-if="['pending', 'confirmed', 'arrived'].includes({{ $r }}.status)">
            <button type="button" @click="openChangeTable({{ $r }})" class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[10.5px] font-bold text-slate-700 hover:border-slate-900">Change Table</button>
        </template>
        <template x-if="!['seated', 'completed', 'cancelled', 'no_show'].includes({{ $r }}.status)">
            <button type="button" @click="openEdit({{ $r }})" class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[10.5px] font-bold text-slate-700 hover:border-slate-900">Edit</button>
        </template>
        <template x-if="['pending', 'confirmed', 'arrived'].includes({{ $r }}.status)">
            <button type="button" @click="markNoShow({{ $r }})" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="rounded-md border border-rose-300 bg-white px-2.5 py-1.5 text-[10.5px] font-bold text-rose-600 hover:border-rose-500 hover:bg-rose-50 disabled:cursor-wait disabled:opacity-50">No Show</button>
        </template>
        <template x-if="!['seated', 'completed', 'cancelled', 'no_show'].includes({{ $r }}.status)">
            <button type="button" @click="openCancel({{ $r }})" class="rounded-md border border-rose-300 bg-white px-2.5 py-1.5 text-[10.5px] font-bold text-rose-600 hover:border-rose-500 hover:bg-rose-50">Cancel</button>
        </template>
    </div>
</div>
