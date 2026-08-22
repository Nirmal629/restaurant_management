@props(['item' => 'i'])

{{--
    BillItemRow — Item / Qty×Rate / Discount / Amount columns, modifiers in
    smaller text beneath. BillItemActions is the ⋮ menu, kept to one glyph so
    the row never gets cluttered (per the brief).
--}}
<div class="bil-item-row rounded-md border px-2.5 py-2"
     :class="{{ $item }}.status === 'cancelled' || {{ $item }}.status === 'refunded' ? 'border-slate-200 bg-slate-50 opacity-60' : 'border-slate-200 bg-white'">

    <div class="min-w-0">
        <p class="truncate text-[12.5px] font-bold text-slate-900"
           :class="({{ $item }}.status === 'cancelled' || {{ $item }}.status === 'refunded') && 'line-through text-slate-500'"
           x-text="{{ $item }}.name"></p>
        <p x-show="{{ $item }}.note" class="truncate text-[10.5px] italic text-slate-500" x-text="{{ $item }}.note"></p>
        <div class="mt-1 flex flex-wrap items-center gap-1">
            <span x-show="{{ $item }}.status !== 'normal'"
                  class="rounded border px-1.5 py-px text-[9px] font-bold uppercase tracking-wide"
                  :class="itemStatusClass({{ $item }}.status)"
                  x-text="itemStatusLabel({{ $item }}.status)"></span>
            <span x-show="{{ $item }}.status === 'complimentary'" class="text-[10px] font-semibold text-violet-600" x-text="'· ' + {{ $item }}.compReason"></span>
            <span x-show="{{ $item }}.status === 'cancelled'" class="text-[10px] font-semibold text-rose-500" x-text="'· ' + {{ $item }}.cancelReason"></span>
            <span x-show="{{ $item }}.status === 'refunded'" class="text-[10px] font-semibold text-rose-500" x-text="'· ' + {{ $item }}.refundReason"></span>
        </div>
    </div>

    <div class="pos-num shrink-0 text-right text-[11.5px] font-semibold text-slate-500" x-text="{{ $item }}.qty + ' × ' + money({{ $item }}.rate)"></div>

    <div class="pos-num shrink-0 w-16 text-right text-[11px] font-semibold text-amber-700" x-text="{{ $item }}.discount ? '− ' + money({{ $item }}.discount) : ''"></div>

    <div class="flex shrink-0 items-center gap-1.5">
        <span class="pos-num w-16 text-right text-[13px] font-black"
              :class="({{ $item }}.status === 'cancelled' || {{ $item }}.status === 'refunded') ? 'text-slate-400' : 'text-slate-900'"
              x-text="money(netAmount({{ $item }}))"></span>

        <div class="relative" x-data @click.outside="itemMenuOpenUid === {{ $item }}.uid && toggleItemMenu({{ $item }}.uid)">
            <button type="button" @click="toggleItemMenu({{ $item }}.uid)"
                    class="grid h-7 w-7 place-items-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Item actions">
                <x-pos.icon name="dots" class="h-4 w-4" stroke="2.4" />
            </button>
            <div x-show="itemMenuOpenUid === {{ $item }}.uid" x-cloak x-transition.origin.top.right
                 class="absolute right-0 top-8 z-30 w-52 overflow-hidden rounded-md border border-slate-200 bg-white py-1 shadow-xl">
                <template x-if="{{ $item }}.status === 'normal' || {{ $item }}.status === 'discounted'">
                    <button type="button" @click="openItemDiscount({{ $item }})" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Apply Item Discount</button>
                </template>
                <template x-if="{{ $item }}.status === 'normal'">
                    <button type="button" @click="openComplimentary({{ $item }})" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Mark Complimentary</button>
                </template>
                <button type="button" @click="viewOrderItem({{ $item }})" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Order Item</button>
                <button type="button" @click="viewKot({{ $item }})" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View KOT</button>
                <template x-if="{{ $item }}.status === 'normal' || {{ $item }}.status === 'discounted'">
                    <button type="button" @click="openCancelItem({{ $item }})" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Cancel Item</button>
                </template>
            </div>
        </div>
    </div>
</div>
