@props(['ticket' => 't'])

{{--
    KotCard — table/token + wait time are the two loudest elements (section 9).
    One primary action per status; item-level actions live inside KotItemRow
    once the ticket reaches PREPARING.
--}}
<div class="relative cursor-pointer rounded-md border-2 bg-white p-2.5 shadow-sm hover:shadow-md"
     @click="openDetail({{ $ticket }})"
     :class="{
        new: 'border-slate-300',
        accepted: 'border-sky-300',
        preparing: waitLevel({{ $ticket }}) === 'critical' ? 'border-rose-400' : 'border-orange-300',
        ready: 'border-emerald-400',
      }[{{ $ticket }}.status]">

    {{-- Priority ribbon corner --}}
    <div class="kds-ribbon" x-show="{{ $ticket }}.priority !== 'normal'">
        <x-kds.priority-badge :ticket="$ticket" />
    </div>

    {{-- KotCardHeader --}}
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <span class="kds-card-title pos-num font-black leading-none text-slate-900" x-text="orderLabel({{ $ticket }})"></span>
                <x-kds.order-type-badge :ticket="$ticket" />
                <span x-show="{{ $ticket }}.round > 1" class="rounded bg-indigo-100 px-1.5 py-px text-[9px] font-black uppercase tracking-wide text-indigo-800">Add-On KOT</span>
            </div>
            <p class="pos-num mt-0.5 text-[10.5px] font-semibold text-slate-500">
                KOT #<span x-text="{{ $ticket }}.kot"></span> · <span x-text="{{ $ticket }}.orderCode"></span>
            </p>
        </div>
        <x-kds.wait-indicator :ticket="$ticket" />
    </div>

    {{-- Waiter / guests --}}
    <p x-show="{{ $ticket }}.waiter || {{ $ticket }}.guests" class="kds-hide-secondary mt-1 kds-card-text font-semibold text-slate-500">
        <span x-show="{{ $ticket }}.waiter" x-text="'Waiter: ' + {{ $ticket }}.waiter"></span>
        <span x-show="{{ $ticket }}.guests" x-text="' · ' + {{ $ticket }}.guests + ' Guests'"></span>
    </p>

    {{-- Partial-ready summary --}}
    <div x-show="isPartiallyReady({{ $ticket }})" class="mt-1.5 flex items-center gap-1.5 rounded bg-emerald-50 px-2 py-1 text-[10.5px] font-bold text-emerald-800">
        <x-pos.icon name="check" class="h-3.5 w-3.5" stroke="2.4" />
        <span x-text="countReady({{ $ticket }}) + ' / ' + countTotal({{ $ticket }}) + ' items ready'"></span>
    </div>

    {{-- Items --}}
    <div class="mt-2 space-y-1.5" @click.stop>
        <template x-for="i in itemsForStation({{ $ticket }}, activeStation)" :key="i.uid">
            <div><x-kds.item-row :ticket="$ticket" item="i" /></div>
        </template>
    </div>

    {{-- Ready-column extras --}}
    <template x-if="{{ $ticket }}.status === 'ready'">
        <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-1.5" @click.stop>
            <span class="text-[10.5px] font-bold text-emerald-700" x-text="'Ready for ' + readyForMinutes({{ $ticket }}) + ' min'"></span>
            <span x-show="{{ $ticket }}.waiterNotified" class="rounded bg-emerald-100 px-1.5 py-px text-[9px] font-black uppercase tracking-wide text-emerald-800">Waiter Notified</span>
        </div>
    </template>

    {{-- Primary action --}}
    <div class="mt-2" @click.stop>
        <template x-if="{{ $ticket }}.status === 'new'">
            <button type="button" @click="acceptTicket({{ $ticket }})" :disabled="saving" :aria-busy="saving ? 'true' : 'false'"
                    class="flex h-11 w-full items-center justify-center gap-1.5 rounded-md bg-slate-900 text-[13px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-wait disabled:bg-slate-300">
                Accept
            </button>
        </template>
        <template x-if="{{ $ticket }}.status === 'accepted'">
            <button type="button" @click="startPreparing({{ $ticket }})" :disabled="saving" :aria-busy="saving ? 'true' : 'false'"
                    class="flex h-11 w-full items-center justify-center gap-1.5 rounded-md bg-orange-500 text-[13px] font-black uppercase tracking-wide text-white hover:bg-orange-400 disabled:cursor-wait disabled:bg-slate-300">
                Start Preparing
            </button>
        </template>
        <template x-if="{{ $ticket }}.status === 'preparing'">
            <button type="button" @click="markAllReady({{ $ticket }})" :disabled="saving" :aria-busy="saving ? 'true' : 'false'"
                    class="flex h-11 w-full items-center justify-center gap-1.5 rounded-md bg-emerald-600 text-[13px] font-black uppercase tracking-wide text-white hover:bg-emerald-500 disabled:cursor-wait disabled:bg-slate-300">
                Mark Ready
            </button>
        </template>
        <template x-if="{{ $ticket }}.status === 'ready'">
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" @click="notifyWaiter({{ $ticket }})" :disabled="{{ $ticket }}.waiterNotified"
                        class="h-10 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900 disabled:cursor-not-allowed disabled:opacity-40">
                    Notify Waiter
                </button>
                <button type="button" @click="markPickedUp({{ $ticket }})" :disabled="saving" :aria-busy="saving ? 'true' : 'false'"
                        class="h-10 rounded-md bg-slate-900 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-wait disabled:bg-slate-300">
                    Picked Up
                </button>
            </div>
        </template>
    </div>
</div>
