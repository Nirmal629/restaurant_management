{{--
    PosActionBar — always docked. SEND KOT / BILL / PAY are never nested inside
    a menu; everything else lives behind MORE so the bar stays two rows tall.
--}}
<div class="pos-dock relative border-t border-slate-200 bg-white px-2 pb-2 pt-2">

    {{-- Primary operational action ------------------------------------- --}}
    <button type="button" @click="sendKot()" :disabled="saving || !unsentLines.length" :aria-busy="saving ? 'true' : 'false'"
            :class="unsentLines.length && !saving
                ? 'bg-amber-500 text-slate-950 hover:bg-amber-400 active:bg-amber-600'
                : 'cursor-not-allowed bg-slate-100 text-slate-400'"
            class="mb-1.5 flex h-11 w-full items-center justify-center gap-2 rounded-md text-[13.5px] font-black uppercase tracking-[0.08em]">
        <x-pos.icon name="chef" class="h-4 w-4" stroke="2" />
        <span>Send KOT</span>
        <span x-show="unsentLines.length"
              class="pos-num rounded bg-slate-950/15 px-1.5 py-px text-[11px] font-bold"
              x-text="unsentCount + ' item(s)'"></span>
        <kbd class="hidden rounded bg-slate-950/10 px-1 text-[9.5px] font-bold min-[1200px]:inline">F6</kbd>
    </button>

    {{-- Secondary + closing actions ------------------------------------- --}}
    <div class="grid grid-cols-4 gap-1.5">

        <button type="button" @click="hold()"
                :class="held ? 'border-amber-400 bg-amber-50 text-amber-800' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-900'"
                class="flex h-10 flex-col items-center justify-center gap-0.5 rounded-md border text-[10.5px] font-bold uppercase tracking-wide">
            <x-pos.icon name="hold" class="h-4 w-4" />
            <span x-text="held ? 'Resume' : 'Hold'"></span>
        </button>

        <button type="button" @click="moreOpen = !moreOpen"
                :class="moreOpen ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-900'"
                class="flex h-10 flex-col items-center justify-center gap-0.5 rounded-md border text-[10.5px] font-bold uppercase tracking-wide">
            <x-pos.icon name="dots" class="h-4 w-4" stroke="2.6" />
            More
        </button>

        <button type="button" @click="moveToBilling()" :disabled="saving" :aria-busy="saving ? 'true' : 'false'"
                class="flex h-10 flex-col items-center justify-center gap-0.5 rounded-md bg-slate-800 text-[10.5px] font-bold uppercase tracking-wide text-white hover:bg-slate-900">
            <x-pos.icon name="receipt" class="h-4 w-4" />
            Bill
        </button>

        <button type="button" @click="openPayment()" :disabled="saving" :aria-busy="saving ? 'true' : 'false'"
                class="flex h-10 flex-col items-center justify-center gap-0.5 rounded-md bg-emerald-600 text-[10.5px] font-black uppercase tracking-wide text-white hover:bg-emerald-500 active:bg-emerald-700">
            <x-pos.icon name="cash" class="h-4 w-4" />
            Pay
        </button>
    </div>

    {{-- MORE ▾ — opens upward; the bar itself never grows -------------- --}}
    <div x-show="moreOpen" x-cloak @click.outside="moreOpen = false"
         class="absolute bottom-[52px] left-2 right-2 z-40 overflow-hidden rounded-md border border-slate-300 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-3 py-1.5">
            <span class="text-[10px] font-black uppercase tracking-[0.09em] text-slate-500">Order actions</span>
            <button type="button" @click="moreOpen = false" class="grid h-5 w-5 place-items-center rounded text-slate-400 hover:bg-slate-200 hover:text-slate-700">
                <x-pos.icon name="x" class="h-3.5 w-3.5" stroke="2.2" />
            </button>
        </div>
        <div class="max-h-[46vh] overflow-y-auto pos-scroll">
            <template x-for="a in moreActions" :key="a.key">
                <button type="button" @click="runMore(a.key)"
                        :class="a.danger ? 'text-rose-600 hover:bg-rose-50' : 'text-slate-700 hover:bg-slate-100'"
                        class="flex w-full items-center gap-2 border-b border-slate-100 px-3 py-2 text-left text-[12.5px] font-semibold last:border-0">
                    <span class="flex-1" x-text="a.label"></span>
                    <kbd x-show="a.hint" class="rounded bg-slate-100 px-1 text-[9.5px] font-bold text-slate-500" x-text="a.hint"></kbd>
                    <x-pos.icon name="chevron-right" class="h-3.5 w-3.5 text-slate-300" />
                </button>
            </template>
        </div>
    </div>
</div>
