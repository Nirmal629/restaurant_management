{{--
    CurrentOrderPanel — header / scrolling item list / totals / actions.
    Only the middle band scrolls: totals and the action bar are pos-dock.
--}}
<aside class="pos-cart border-l border-slate-200 bg-slate-50">

    {{-- Header ---------------------------------------------------------- --}}
    <div class="pos-dock border-b border-slate-200 bg-white px-3 py-1.5">
        <div class="flex items-center gap-2">
            <h2 class="text-[12px] font-bold uppercase tracking-[0.07em] text-slate-900">Current Order</h2>
            <span class="pos-num rounded bg-slate-900 px-1.5 py-px text-[9.5px] font-bold uppercase tracking-wide text-white"
                  x-text="'Round ' + round"></span>
            <span x-show="readyCount"
                  class="pos-num rounded border border-emerald-400 bg-emerald-50 px-1.5 py-px text-[9.5px] font-bold uppercase tracking-wide text-emerald-800">
                <span x-text="readyCount"></span> Ready
            </span>
            <span class="flex-1"></span>
            <button type="button" @click="open('kot')"
                    class="flex items-center gap-1 rounded border border-slate-200 px-1.5 py-1 text-[10.5px] font-bold text-slate-600 hover:border-slate-900 hover:text-slate-900">
                <x-pos.icon name="receipt" class="h-3.5 w-3.5" /> KOT History
            </button>
        </div>

        <p class="pos-num mt-0.5 truncate text-[10.5px] font-semibold text-slate-500">
            <span x-text="'#' + order.code"></span>
            <template x-if="orderType === 'dinein'">
                <span><span x-text="' · Table ' + order.table"></span><span x-text="' · ' + order.guests + ' guests'"></span><span x-text="' · ' + order.waiter"></span></span>
            </template>
            <template x-if="orderType === 'takeaway'">
                <span x-text="' · Token ' + order.token + ' · Pickup ' + order.pickupAt"></span>
            </template>
            <template x-if="orderType === 'delivery'">
                <span x-text="' · ' + (order.deliveryMode === 'own' ? 'Own delivery' : (order.aggregator || 'Aggregator'))"></span>
            </template>
            <span x-show="order.customer" x-text="' · ' + (order.customer?.name ?? '')"></span>
        </p>
    </div>

    {{-- Scrolling item list --------------------------------------------- --}}
    <div class="pos-scroll px-2 py-2" x-ref="cartScroll">

        {{-- Empty state --}}
        <div x-show="!cart.length" class="flex flex-col items-center gap-2 py-20 text-center">
            <x-pos.icon name="receipt" class="h-8 w-8 text-slate-300" />
            <p class="text-[13px] font-semibold text-slate-500">No items yet</p>
            <p class="max-w-[220px] text-[11.5px] text-slate-400">Tap a menu item to add it. Right-click a tile for options and instructions.</p>
        </div>

        {{-- NEW · not yet sent — pinned first so the working round is always visible --}}
        <template x-if="unsentLines.length">
            <section class="mb-2">
                <div class="pos-group-head -mx-2 mb-1.5 flex items-center gap-2 border-y border-amber-300 bg-amber-100/90 px-3 py-1 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.09em] text-amber-900">New · not sent</span>
                    <span class="pos-num rounded bg-amber-500 px-1.5 text-[9.5px] font-bold text-white" x-text="unsentLines.length + ' line(s)'"></span>
                    <span class="flex-1"></span>
                    <span class="pos-num text-[10.5px] font-bold text-amber-900"
                          x-text="money(unsentLines.reduce((s, l) => s + lineTotal(l), 0))"></span>
                </div>
                <div class="space-y-1.5">
                    <template x-for="l in unsentLines" :key="l.uid">
                        <div><x-pos.order-item-row line="l" /></div>
                    </template>
                </div>
            </section>
        </template>

        {{-- Dispatched KOT rounds, newest first --}}
        <template x-for="r in sentRounds" :key="r.kot">
            <section class="mb-2">
                <div class="pos-group-head -mx-2 mb-1.5 flex items-center gap-2 border-y border-slate-200 bg-slate-100/95 px-3 py-1 backdrop-blur">
                    <x-pos.icon name="printer" class="h-3 w-3 text-slate-400" />
                    <span class="pos-num text-[10px] font-black uppercase tracking-[0.09em] text-slate-600"
                          x-text="'KOT #' + r.kot"></span>
                    <span class="pos-num text-[10px] font-semibold text-slate-400" x-text="'sent ' + r.sentAt"></span>
                    <span class="flex-1"></span>
                    <span class="pos-num text-[10.5px] font-bold text-slate-600"
                          x-text="money(r.lines.filter(l => l.status !== 'cancelled').reduce((s, l) => s + lineTotal(l), 0))"></span>
                </div>
                <div class="space-y-1.5">
                    <template x-for="l in r.lines" :key="l.uid">
                        <div><x-pos.order-item-row line="l" /></div>
                    </template>
                </div>
            </section>
        </template>
    </div>

    <x-pos.order-totals />
    <x-pos.action-bar />
</aside>
