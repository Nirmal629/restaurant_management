{{--
    OrderInfoBar — 38/44px. Every field is the control for the thing it shows,
    so changing table/guests/waiter/customer never leaves the POS.
--}}
<div class="pos-infobar z-20 flex items-center gap-2 border-b border-slate-200 bg-white px-3">

    {{-- Order identity (all modes) ------------------------------------ --}}
    <div class="flex shrink-0 items-center gap-2">
        <span class="pos-num rounded bg-slate-900 px-2 py-1 text-[11px] font-bold tracking-wide text-white"
              x-text="'#' + order.code"></span>
        <span x-show="held"
              class="rounded border border-amber-400 bg-amber-50 px-1.5 py-0.5 text-[9.5px] font-bold uppercase tracking-wide text-amber-800">
            On Hold
        </span>
    </div>

    <div class="h-5 w-px shrink-0 bg-slate-200"></div>

    {{-- Mode-specific fields ------------------------------------------ --}}
    <div class="pos-scroll-x pos-no-scrollbar flex min-w-0 flex-1 items-center gap-1.5">

        {{-- DINE IN ---------------------------------------------------- --}}
        <template x-if="orderType === 'dinein'">
            <div class="flex items-center gap-1.5">
                <button type="button" @click="open('table')"
                        class="group flex h-7 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2 hover:border-slate-900 hover:bg-slate-50">
                    <x-pos.icon name="table" class="h-3.5 w-3.5 text-slate-400 group-hover:text-slate-700" />
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Table</span>
                    <span class="pos-num text-[12.5px] font-bold text-slate-900" x-text="order.table"></span>
                    <span class="hidden text-[10.5px] font-medium text-slate-500 lg:inline" x-text="'· ' + order.floor"></span>
                    <kbd class="ml-0.5 hidden rounded bg-slate-100 px-1 text-[9px] font-bold text-slate-400 xl:inline">F4</kbd>
                </button>

                <div class="flex h-7 items-center gap-1 rounded-md border border-slate-300 bg-white pl-2">
                    <x-pos.icon name="users" class="h-3.5 w-3.5 text-slate-400" />
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Guests</span>
                    <button type="button" @click="order.guests = Math.max(1, order.guests - 1)"
                            class="grid h-7 w-6 place-items-center text-slate-500 hover:bg-slate-100" aria-label="Fewer guests">
                        <x-pos.icon name="minus" class="h-3 w-3" stroke="2.4" />
                    </button>
                    <span class="pos-num w-4 text-center text-[12.5px] font-bold text-slate-900" x-text="order.guests"></span>
                    <button type="button" @click="order.guests++"
                            class="grid h-7 w-6 place-items-center rounded-r-md text-slate-500 hover:bg-slate-100" aria-label="More guests">
                        <x-pos.icon name="plus" class="h-3 w-3" stroke="2.4" />
                    </button>
                </div>

                <button type="button" @click="open('waiter')"
                        class="group flex h-7 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2 hover:border-slate-900 hover:bg-slate-50">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Waiter</span>
                    <span class="text-[12px] font-bold text-slate-900" x-text="order.waiter"></span>
                </button>
            </div>
        </template>

        {{-- TAKEAWAY --------------------------------------------------- --}}
        <template x-if="orderType === 'takeaway'">
            <div class="flex items-center gap-1.5">
                <div class="flex h-7 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2">
                    <x-pos.icon name="bag" class="h-3.5 w-3.5 text-slate-400" />
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Token</span>
                    <input x-model="order.token"
                           class="pos-num w-12 border-0 bg-transparent p-0 text-[12.5px] font-bold text-slate-900 focus:outline-none" />
                </div>
                <div class="flex h-7 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Pickup</span>
                    <input x-model="order.pickupAt" placeholder="20:15"
                           class="pos-num w-14 border-0 bg-transparent p-0 text-[12.5px] font-bold text-slate-900 focus:outline-none" />
                </div>
            </div>
        </template>

        {{-- DELIVERY --------------------------------------------------- --}}
        <template x-if="orderType === 'delivery'">
            <div class="flex min-w-0 items-center gap-1.5">
                <div class="flex h-7 shrink-0 rounded-md border border-slate-300 bg-white p-0.5">
                    @foreach ([['own', 'Own Delivery'], ['aggregator', 'Aggregator']] as [$k, $l])
                        <button type="button" @click="order.deliveryMode = '{{ $k }}'"
                                :class="order.deliveryMode === '{{ $k }}' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                class="rounded px-2 text-[10.5px] font-bold uppercase tracking-wide">{{ $l }}</button>
                    @endforeach
                </div>

                <template x-if="order.deliveryMode === 'aggregator'">
                    <select x-model="order.aggregator"
                            class="h-7 shrink-0 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-semibold text-slate-800 focus:outline-none">
                        <option value="">Select channel…</option>
                        <option>Swiggy</option>
                        <option>Zomato</option>
                        <option>Magicpin</option>
                    </select>
                </template>

                <input x-model="order.address" placeholder="Delivery address…"
                       class="h-7 min-w-0 flex-1 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-medium text-slate-800 placeholder:text-slate-400 focus:border-slate-900 focus:outline-none" />
            </div>
        </template>

        {{-- Customer (all modes) --------------------------------------- --}}
        <button type="button" @click="open('customer')"
                :class="order.customer
                    ? 'border-slate-300 bg-white hover:border-slate-900'
                    : 'border-dashed border-slate-400 bg-slate-50 hover:border-slate-900 hover:bg-white'"
                class="group flex h-7 shrink-0 items-center gap-1.5 rounded-md border px-2">
            <template x-if="!order.customer">
                <span class="flex items-center gap-1 text-[11.5px] font-bold text-slate-600 group-hover:text-slate-900">
                    <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.2" /> Add Customer
                    <kbd class="ml-0.5 hidden rounded bg-slate-200 px-1 text-[9px] font-bold text-slate-500 xl:inline">F3</kbd>
                </span>
            </template>
            <template x-if="order.customer">
                <span class="flex items-center gap-1.5">
                    <x-pos.icon name="users" class="h-3.5 w-3.5 text-slate-400" />
                    <span class="text-[12px] font-bold text-slate-900" x-text="order.customer.name"></span>
                    <span class="pos-num hidden text-[10.5px] font-medium text-slate-500 lg:inline" x-text="order.customer.phone"></span>
                    <span x-show="order.customer.tag"
                          class="rounded bg-amber-100 px-1 text-[9px] font-bold uppercase tracking-wide text-amber-800"
                          x-text="order.customer.tag"></span>
                </span>
            </template>
        </button>

        {{-- Order note --------------------------------------------------- --}}
        <button type="button" @click="open('notes')"
                :class="order.notes ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-500 hover:border-slate-900'"
                class="flex h-7 shrink-0 items-center gap-1 rounded-md border px-2 text-[11px] font-bold">
            <x-pos.icon name="note" class="h-3.5 w-3.5" />
            <span x-text="order.notes ? 'Note added' : 'Note'"></span>
        </button>
    </div>

    {{-- Elapsed --------------------------------------------------------- --}}
    <div class="flex shrink-0 items-center gap-1.5 border-l border-slate-200 pl-2.5">
        <x-pos.icon name="clock" class="h-3.5 w-3.5 text-slate-400" />
        <span class="pos-num text-[12px] font-bold"
              :class="duration > 45 ? 'text-rose-600' : duration > 25 ? 'text-amber-600' : 'text-slate-700'"
              x-text="duration + ' min'"></span>
    </div>
</div>
