{{-- KdsHeader — ~56px. Identity · mode label · live telemetry. --}}
<header class="pos-header z-30 flex items-center gap-3 border-b border-slate-800 bg-slate-900 px-3 text-slate-100">

    <a href="{{ route('app.start') }}" class="flex shrink-0 items-center gap-2 rounded px-1 py-1 hover:bg-slate-800">
        <span class="grid h-7 w-7 place-items-center rounded bg-brand-600 text-[11px] font-black text-white">RB</span>
        <span class="hidden leading-none sm:block">
            <span class="block text-[13px] font-bold text-white" x-text="venue.name"></span>
            <span class="mt-0.5 block text-[10px] font-medium text-slate-400" x-text="venue.branch"></span>
        </span>
    </a>

    <div class="h-6 w-px shrink-0 bg-slate-700"></div>

    <div class="flex shrink-0 items-center gap-2">
        <span class="rounded-md bg-slate-800 px-2 py-1.5 text-[11px] font-black uppercase tracking-[0.08em] text-amber-400">Kitchen Display</span>
        <span class="hidden items-center gap-1 rounded-md border border-slate-700 px-2 py-1.5 text-[10.5px] font-bold text-slate-300 md:flex">
            <x-pos.icon name="chef" class="h-3.5 w-3.5 text-slate-400" />
            <span x-text="stationLabel(activeStation)"></span>
        </span>
    </div>

    {{-- Kitchen View / Expeditor View toggle --}}
    <div class="mx-auto flex shrink-0 rounded-md bg-slate-800 p-0.5">
        <button type="button" @click="viewMode = 'kitchen'"
                :class="viewMode === 'kitchen' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:text-white'"
                class="rounded px-3 py-1.5 text-[11.5px] font-bold uppercase tracking-[0.05em]">Kitchen View</button>
        <button type="button" @click="viewMode = 'expeditor'"
                :class="viewMode === 'expeditor' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:text-white'"
                class="rounded px-3 py-1.5 text-[11.5px] font-bold uppercase tracking-[0.05em]">Expeditor View</button>
    </div>

    <div class="flex shrink-0 items-center gap-1.5">

        {{-- Connection --}}
        <button type="button" @click="toggleConnection()"
                :class="connected ? 'border-slate-700 text-emerald-400' : 'border-rose-500 bg-rose-500/10 text-rose-400'"
                class="flex h-8 items-center gap-1.5 rounded-md border px-2 text-[10px] font-bold uppercase tracking-wide"
                title="Click to preview the disconnected state">
            <span class="h-1.5 w-1.5 rounded-full" :class="connected ? 'bg-emerald-400' : 'bg-rose-500 kds-pulse'"></span>
            <span x-text="connected ? 'Live' : 'Connection Lost'"></span>
        </button>

        {{-- Printer --}}
        <button type="button" @click="togglePrinter()"
                :class="printerReady ? 'border-slate-700 text-slate-300' : 'border-amber-500 bg-amber-500/10 text-amber-400'"
                class="hidden h-8 items-center gap-1.5 rounded-md border px-2 text-[10px] font-bold uppercase tracking-wide lg:flex"
                title="Click to preview printer offline">
            <x-pos.icon name="printer" class="h-3.5 w-3.5" />
            <span x-text="printerReady ? 'Printer Ready' : 'Printer Offline'"></span>
        </button>

        {{-- Sound --}}
        <div class="relative" x-data @click.outside="soundOpen = false">
            <button type="button" @click="soundOpen = !soundOpen"
                    :class="soundOpen ? 'border-slate-500 bg-slate-800' : 'border-slate-700'"
                    class="grid h-8 w-8 place-items-center rounded-md border text-slate-300 hover:border-slate-500">
                <template x-if="soundOn"><span><x-pos.icon name="volume" class="h-4 w-4" /></span></template>
                <template x-if="!soundOn"><span><x-pos.icon name="volume-off" class="h-4 w-4" /></span></template>
            </button>
            <div x-show="soundOpen" x-cloak x-transition.origin.top.right
                 class="absolute right-0 top-9 z-30 w-56 rounded-md border border-slate-700 bg-slate-900 p-3 shadow-2xl">
                <button type="button" @click="soundOn = !soundOn"
                        class="mb-2 flex w-full items-center justify-between rounded-md border border-slate-700 px-2.5 py-1.5">
                    <span class="text-[11.5px] font-bold text-slate-200">Sound</span>
                    <span class="rounded px-1.5 py-0.5 text-[9.5px] font-black uppercase tracking-wide"
                          :class="soundOn ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-400'"
                          x-text="soundOn ? 'On' : 'Off'"></span>
                </button>
                <div class="space-y-1.5" :class="!soundOn && 'opacity-40 pointer-events-none'">
                    <template x-for="[key, label] in [['newKot','New KOT'],['delayed','Delayed Ticket'],['ready','Ready Confirmation']]" :key="key">
                        <label class="flex items-center justify-between text-[11px] font-semibold text-slate-300">
                            <span x-text="label"></span>
                            <input type="checkbox" x-model="soundModes[key]" class="h-3.5 w-3.5 accent-amber-400">
                        </label>
                    </template>
                </div>
            </div>
        </div>

        {{-- TV mode --}}
        <button type="button" @click="tvMode = !tvMode"
                :class="tvMode ? 'border-amber-400 bg-amber-400 text-slate-950' : 'border-slate-700 text-slate-300 hover:border-slate-500'"
                class="hidden h-8 items-center gap-1.5 rounded-md border px-2.5 text-[10.5px] font-bold uppercase tracking-wide sm:flex">
            <span x-text="tvMode ? 'TV Mode' : 'Standard'"></span>
        </button>

        {{-- Full screen --}}
        <button type="button" @click="toggleFullscreen()"
                class="grid h-8 w-8 place-items-center rounded-md border border-slate-700 text-slate-300 hover:border-slate-500" title="Full screen">
            <x-pos.icon name="expand" class="h-4 w-4" />
        </button>

        <div class="h-6 w-px bg-slate-700"></div>

        <div class="text-right leading-none">
            <p class="pos-num text-[13px] font-bold text-white" x-text="clock"></p>
            <p class="mt-0.5 text-[9.5px] font-bold uppercase tracking-[0.08em] text-emerald-400">Shift <span x-text="operator.shift || 'Full Day'"></span></p>
        </div>
        <span class="hidden items-center gap-1.5 rounded-md px-1.5 py-1 lg:flex">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-700 text-[11px] font-bold text-white" x-text="operator.initials"></span>
            <span class="text-left leading-none">
                <span class="block text-[11.5px] font-bold text-white" x-text="[(operator.role || ''), (operator.name || 'Kitchen')].filter(Boolean).join(' ')"></span>
            </span>
        </span>
    </div>
</header>
