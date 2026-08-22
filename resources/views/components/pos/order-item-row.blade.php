@props(['line' => 'l'])

{{--
    OrderItemRow. Two densities in one component:
      · unsent  → full edit affordances (stepper, edit, remove)
      · sent+   → no silent removal; Add More / Cancel (reason) / View KOT
--}}
<div :class="{{ $line }}.status === 'unsent'
        ? 'border-slate-300 bg-white'
        : {{ $line }}.status === 'cancelled'
            ? 'border-slate-200 bg-slate-50 opacity-70'
            : 'border-slate-200 bg-white'"
     class="relative rounded-md border px-2 py-1.5">

    {{-- Left rail: amber = still editable, slate = already in the kitchen --}}
    <span :class="{{ $line }}.status === 'unsent' ? 'bg-amber-400' : {{ $line }}.status === 'ready' ? 'bg-emerald-500' : 'bg-slate-300'"
          class="absolute inset-y-1 left-0 w-[3px] rounded-full"></span>

    {{-- Row 1 — qty · name · line total --}}
    <div class="flex items-start gap-2 pl-1.5">
        <span class="pos-num mt-px shrink-0 rounded bg-slate-900 px-1.5 py-px text-[11px] font-bold text-white"
              :class="{{ $line }}.status === 'cancelled' && 'bg-slate-400'"
              x-text="{{ $line }}.qty + '×'"></span>

        <x-pos.diet-mark expr="{{ $line }}.diet" size="h-3 w-3 mt-[3px]" />

        <span class="min-w-0 flex-1 text-[12.5px] font-semibold leading-[1.3] text-slate-900"
              :class="{{ $line }}.status === 'cancelled' && 'text-slate-500 line-through'"
              x-text="{{ $line }}.name"></span>

        <span class="pos-num shrink-0 text-right text-[12.5px] font-bold text-slate-900"
              :class="{{ $line }}.status === 'cancelled' && 'text-slate-400 line-through'"
              x-text="money(lineTotal({{ $line }}))"></span>
    </div>

    {{-- Row 2 — variant, modifiers, instruction (only when present) --}}
    <div x-show="{{ $line }}.variant || {{ $line }}.modifiers.length || {{ $line }}.note || {{ $line }}.cancelReason"
         class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 pl-[26px]">
        <span x-show="{{ $line }}.variant"
              class="rounded bg-slate-100 px-1 text-[10px] font-bold text-slate-700" x-text="{{ $line }}.variant"></span>
        <template x-for="m in {{ $line }}.modifiers" :key="m.label">
            <span class="pos-num rounded bg-slate-100 px-1 text-[10px] font-medium text-slate-600"
                  x-text="'+ ' + m.label + (m.delta ? ' ' + money(m.delta) : '')"></span>
        </template>
        <span x-show="{{ $line }}.note"
              class="text-[10.5px] font-medium italic text-amber-700" x-text="'“' + {{ $line }}.note + '”'"></span>
        <span x-show="{{ $line }}.cancelReason"
              class="text-[10.5px] font-medium text-rose-600" x-text="'Cancelled: ' + {{ $line }}.cancelReason"></span>
    </div>

    {{-- Row 3 — status + provenance · actions --}}
    <div class="mt-1 flex items-center gap-1.5 pl-[26px]">
        <x-pos.status-badge expr="{{ $line }}.status" />

        <span x-show="{{ $line }}.kot" class="pos-num text-[10px] font-semibold text-slate-400"
              x-text="'KOT #' + {{ $line }}.kot + ' · ' + {{ $line }}.sentAt"></span>
        <span x-show="!{{ $line }}.kot" class="text-[10px] font-semibold text-slate-400"
              x-text="{{ $line }}.station"></span>

        <span class="flex-1"></span>

        {{-- Editable line --}}
        <template x-if="{{ $line }}.status === 'unsent'">
            <span class="flex items-center gap-1">
                <button type="button" @click="editLine({{ $line }})"
                        class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900 hover:text-slate-900"
                        title="Edit options / instructions">
                    <x-pos.icon name="edit" class="h-3.5 w-3.5" />
                </button>
                <button type="button" @click="removeLine({{ $line }}.uid)"
                        class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-500 hover:border-rose-500 hover:bg-rose-50 hover:text-rose-600"
                        title="Remove line">
                    <x-pos.icon name="trash" class="h-3.5 w-3.5" />
                </button>
                <x-pos.qty-control size="sm"
                                   dec="bump({{ $line }}.uid, -1)"
                                   inc="bump({{ $line }}.uid, 1)"
                                   value="{{ $line }}.qty" />
            </span>
        </template>

        {{-- Dispatched line — additive only --}}
        <template x-if="{{ $line }}.status !== 'unsent' && {{ $line }}.status !== 'cancelled'">
            <span class="flex items-center gap-1">
                <button type="button" @click="addMore({{ $line }})"
                        class="flex h-7 items-center gap-1 rounded border border-slate-200 px-1.5 text-[10.5px] font-bold text-slate-600 hover:border-slate-900 hover:text-slate-900"
                        title="Add another of this item to the next KOT">
                    <x-pos.icon name="plus" class="h-3 w-3" stroke="2.4" /> Add
                </button>
                <button type="button" @click="open('kot')"
                        class="grid h-7 w-7 place-items-center rounded border border-slate-200 text-slate-500 hover:border-slate-900 hover:text-slate-900"
                        title="View KOT">
                    <x-pos.icon name="receipt" class="h-3.5 w-3.5" />
                </button>
                <button type="button" @click="askCancel({{ $line }})"
                        class="flex h-7 items-center rounded border border-slate-200 px-1.5 text-[10.5px] font-bold text-slate-500 hover:border-rose-500 hover:bg-rose-50 hover:text-rose-600"
                        title="Cancel item (reason required)">
                    Cancel
                </button>
            </span>
        </template>
    </div>
</div>
