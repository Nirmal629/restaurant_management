{{-- Transient confirmation toast — same pattern as the POS terminal. --}}
<div x-show="toast" x-cloak class="pointer-events-none fixed left-1/2 top-3 z-[100] -translate-x-1/2">
    <div :class="{
            success: 'border-emerald-500 bg-emerald-600 text-white',
            warn: 'border-amber-500 bg-amber-500 text-slate-950',
            info: 'border-slate-700 bg-slate-900 text-white',
         }[toast?.tone ?? 'info']"
         class="flex items-center gap-2 rounded-md border px-3.5 py-2 shadow-xl">
        <x-pos.icon name="check" class="h-4 w-4" stroke="2.6" />
        <span class="text-[12.5px] font-bold" x-text="toast?.message"></span>
    </div>
</div>
