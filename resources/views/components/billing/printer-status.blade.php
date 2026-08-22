{{-- PrinterIndicator — compact, click to preview the offline state (design-only, no hardware). --}}
<button type="button" @click="printerReady = !printerReady"
        :class="printerReady ? 'border-slate-700 text-slate-300' : 'border-amber-500 bg-amber-500/10 text-amber-400'"
        class="hidden h-8 items-center gap-1.5 rounded-md border px-2 text-[10px] font-bold uppercase tracking-wide md:flex"
        title="Click to preview printer offline">
    <x-pos.icon name="printer" class="h-3.5 w-3.5" />
    <span x-text="printerReady ? 'Printer Ready' : 'Printer Offline'"></span>
</button>
