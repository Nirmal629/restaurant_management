{{-- BillingOrderInfo — 38/44px. Mode-specific fields, same pattern as the POS/Floor info bars. --}}
<div class="pos-infobar z-20 flex items-center gap-2 border-b border-slate-200 bg-white px-3">

    <template x-if="order.type === 'dinein'">
        <div class="flex items-center gap-3 text-[12px]">
            <span class="flex items-center gap-1.5"><span class="font-semibold text-slate-500">Table</span> <span class="pos-num font-black text-slate-900" x-text="order.table"></span></span>
            <span class="text-slate-300">·</span>
            <span class="text-slate-500" x-text="order.floor"></span>
            <span class="text-slate-300">·</span>
            <span class="flex items-center gap-1"><span class="font-semibold text-slate-500">Guests</span> <span class="pos-num font-bold text-slate-800" x-text="order.guests"></span></span>
            <span class="text-slate-300">·</span>
            <span class="flex items-center gap-1"><span class="font-semibold text-slate-500">Waiter</span> <span class="font-bold text-slate-800" x-text="order.waiter"></span></span>
        </div>
    </template>
    <template x-if="order.type !== 'dinein'">
        <span class="rounded bg-slate-800 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-white" x-text="order.type"></span>
    </template>

    <div class="h-5 w-px bg-slate-200"></div>

    <button type="button" @click="open('customer')"
            class="flex items-center gap-1.5 text-[12px] font-bold text-slate-800 hover:text-slate-950">
        <x-pos.icon name="users" class="h-3.5 w-3.5 text-slate-400" />
        <span x-text="customer.name"></span>
        <x-pos.icon name="edit" class="h-3 w-3 text-slate-300" />
    </button>

    <span class="flex-1"></span>

    <div class="flex items-center gap-3 text-[11.5px] text-slate-500">
        <span>Started <span class="pos-num font-bold text-slate-800" x-text="order.startedMinutesAgo > 60 ? Math.floor(order.startedMinutesAgo/60)+'h '+(order.startedMinutesAgo%60)+'m ago' : order.startedMinutesAgo + 'm ago'"></span></span>
        <span>Duration <span class="pos-num font-bold text-slate-800" x-text="order.startedMinutesAgo + ' min'"></span></span>
    </div>
</div>
