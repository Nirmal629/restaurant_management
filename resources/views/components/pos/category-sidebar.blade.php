{{--
    CategorySidebar — 174px labelled list on desktop, 76px icon rail below
    1200px. Scrolls internally; the page never does.
--}}
<aside class="pos-rail border-r border-slate-200 bg-white">
    <nav class="pos-scroll p-1.5" aria-label="Menu categories">
        <template x-for="(cat, i) in categories" :key="cat.key">
            <div>
                {{-- Rule between the pinned shortcuts and the real menu tree --}}
                <div x-show="i === 3" class="my-1.5 border-t border-slate-200"></div>

                <button type="button"
                        @click="activeCat = cat.key"
                        :aria-current="activeCat === cat.key"
                        :class="activeCat === cat.key
                            ? 'bg-slate-900 text-white'
                            : 'text-slate-700 hover:bg-slate-100'"
                        class="mb-0.5 flex w-full items-center gap-2 rounded-md px-2 py-2 text-left min-[1200px]:py-[7px] max-[1199px]:flex-col max-[1199px]:gap-1 max-[1199px]:px-1 max-[1199px]:py-1.5">

                    {{-- Icon: the fixed anchor in both layouts. Blade cannot pick an
                         SVG from a runtime value, so all glyphs render and Alpine
                         reveals the matching one. --}}
                    <span class="grid h-5 w-5 shrink-0 place-items-center max-[1199px]:h-6 max-[1199px]:w-6">
                        @foreach (['grid', 'star', 'clock', 'starter', 'biryani', 'curry', 'noodle', 'flame', 'rice', 'bread', 'dessert', 'drink', 'combo'] as $ic)
                            <x-pos.icon name="{{ $ic }}" class="col-start-1 row-start-1 h-[18px] w-[18px]"
                                        x-show="cat.icon === '{{ $ic }}'" />
                        @endforeach
                    </span>

                    <span class="min-w-0 flex-1 truncate text-[12.5px] font-semibold max-[1199px]:w-full max-[1199px]:flex-none max-[1199px]:text-center max-[1199px]:text-[9.5px] max-[1199px]:font-bold max-[1199px]:leading-tight"
                          x-text="cat.label"></span>

                    <span :class="activeCat === cat.key ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500'"
                          class="pos-num rounded px-1.5 py-px text-[10px] font-bold max-[1199px]:hidden"
                          x-text="categoryCount(cat.key)"></span>
                </button>
            </div>
        </template>
    </nav>

    {{-- Rail footer: shortcut sheet is one tap away on touch terminals --}}
    <div class="pos-dock border-t border-slate-200 p-1.5">
        <button type="button" @click="open('shortcuts')"
                class="flex w-full items-center justify-center gap-1.5 rounded-md border border-slate-200 py-1.5 text-[10.5px] font-bold uppercase tracking-wide text-slate-500 hover:border-slate-400 hover:text-slate-800">
            <x-pos.icon name="keyboard" class="h-3.5 w-3.5" />
            <span class="max-[1199px]:hidden">Shortcuts</span>
        </button>
    </div>
</aside>
