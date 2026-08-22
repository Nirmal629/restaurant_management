@props(['ticket' => 't', 'item' => 'i'])

{{--
    KotItemRow. Quantity + name are the loudest thing in the row; special
    instructions and allergies get their own emphasized block underneath —
    never buried in small type, per the brief.
--}}
<div class="rounded-md border px-2 py-1.5"
     :class="{{ $item }}.status === 'ready' ? 'border-emerald-300 bg-emerald-50' :
             ({{ $item }}.status === 'unavailable' || {{ $item }}.status === 'cancelled') ? 'border-slate-200 bg-slate-50 opacity-70' :
             {{ $item }}.status === 'cancel_requested' ? 'border-amber-400 bg-amber-50' :
             {{ $item }}.fire === 'hold' ? 'border-dashed border-slate-300 bg-slate-50' :
             'border-slate-200 bg-white'">

    <div class="flex items-start gap-2">
        <span class="pos-num kds-card-title shrink-0 font-black text-slate-900" x-text="{{ $item }}.qty + '×'"></span>
        <div class="min-w-0 flex-1">
            <p class="kds-card-title font-bold leading-tight text-slate-900"
               :class="({{ $item }}.status === 'unavailable' || {{ $item }}.status === 'cancelled') && 'line-through text-slate-400'"
               x-text="{{ $item }}.name"></p>
            <p x-show="activeStation === 'all'" class="mt-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-400" x-text="stationLabel({{ $item }}.station)"></p>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            {{-- Fire/Hold chip --}}
            <span x-show="{{ $item }}.course" class="rounded px-1.5 py-px text-[9px] font-black uppercase tracking-wide"
                  :class="{{ $item }}.fire === 'hold' ? 'bg-slate-200 text-slate-600' : 'bg-orange-100 text-orange-800 border border-orange-300'"
                  x-text="{{ $item }}.fire === 'hold' ? 'Hold' : 'Fire Now'"></span>

            <span class="rounded border px-1.5 py-px text-[9px] font-bold uppercase tracking-wide"
                  :class="itemStatusClass({{ $item }}.status)"
                  x-text="itemStatusLabel({{ $item }}.status)"></span>
        </div>
    </div>

    {{-- Variant / modifiers --}}
    <div x-show="{{ $item }}.variant || {{ $item }}.modifiers.length" class="mt-1 flex flex-wrap gap-1 pl-[26px]">
        <span x-show="{{ $item }}.variant" class="rounded bg-slate-100 px-1.5 py-px text-[10px] font-bold text-slate-700" x-text="{{ $item }}.variant"></span>
        <template x-for="m in {{ $item }}.modifiers" :key="m">
            <span class="rounded bg-slate-100 px-1.5 py-px text-[10px] font-medium text-slate-600" x-text="'+ ' + m"></span>
        </template>
    </div>

    {{-- Special instruction — emphasized, never tiny --}}
    <div x-show="{{ $item }}.note" class="mt-1 ml-[26px] rounded border-l-4 border-amber-500 bg-amber-50 px-2 py-1">
        <p class="kds-card-text font-bold uppercase tracking-wide text-amber-900" x-text="{{ $item }}.note"></p>
    </div>

    {{-- Allergy — strongest treatment on the card --}}
    <div x-show="{{ $item }}.allergy" class="ml-[26px]">
        <x-kds.allergy-alert :text="$item . '.allergy'" />
    </div>

    {{-- Unavailable reason --}}
    <p x-show="{{ $item }}.status === 'unavailable'" class="mt-1 ml-[26px] text-[10.5px] font-semibold text-rose-600" x-text="'Reason: ' + ({{ $item }}.unavailableReason || '—')"></p>

    {{-- Cancel-request banner — visible immediately, not blocking --}}
    <div x-show="{{ $item }}.status === 'cancel_requested'" class="mt-1.5 ml-[26px] flex items-center gap-2 rounded border border-amber-400 bg-amber-100 px-2 py-1">
        <x-pos.icon name="alert" class="h-3.5 w-3.5 shrink-0 text-amber-800" />
        <span class="flex-1 text-[10.5px] font-bold text-amber-900" x-text="{{ $item }}.note || 'Cancel requested from POS'"></span>
        <button type="button" @click.stop="acknowledgeCancel({{ $ticket }}, {{ $item }})"
                class="shrink-0 rounded bg-amber-600 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-white hover:bg-amber-500">Acknowledge</button>
    </div>

    {{-- Item-level actions — only meaningful once the ticket is actively being cooked --}}
    <div x-show="{{ $ticket }}.status === 'preparing' && {{ $item }}.status === 'pending' && {{ $item }}.fire !== 'hold'"
         class="mt-1.5 flex justify-end gap-1.5 pl-[26px]">
        <button type="button" @click.stop="openUnavailable({{ $ticket }}, {{ $item }})"
                class="rounded-md border border-slate-300 bg-white px-2 py-1.5 text-[10.5px] font-bold text-slate-600 hover:border-rose-400 hover:text-rose-600">Unavailable</button>
        <button type="button" @click.stop="markItemReady({{ $ticket }}, {{ $item }})"
                class="rounded-md bg-emerald-600 px-3 py-1.5 text-[10.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500">Mark Ready</button>
    </div>

    {{-- Fire button for a held course item --}}
    <div x-show="{{ $item }}.fire === 'hold'" class="mt-1.5 flex justify-end pl-[26px]">
        <button type="button" @click.stop="fireCourse({{ $ticket }}, {{ $item }}.course)"
                class="flex items-center gap-1 rounded-md bg-orange-500 px-3 py-1.5 text-[10.5px] font-black uppercase tracking-wide text-white hover:bg-orange-400">
            <x-pos.icon name="flame" class="h-3.5 w-3.5" /> Fire Now
        </button>
    </div>
</div>
