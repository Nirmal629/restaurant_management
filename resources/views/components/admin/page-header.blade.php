@props(['title', 'subtitle' => null])

{{-- PageHeader — compact, matches the POS/Floor/KDS/Billing header density. Actions go in the slot. --}}
<header class="adm-header flex items-center gap-3 border-b border-slate-200 bg-white px-4">
    <div class="min-w-0">
        <h1 class="truncate text-[15px] font-black text-slate-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="truncate text-[10.5px] font-medium text-slate-400">{{ $subtitle }}</p>
        @endif
    </div>
    <span class="flex-1"></span>
    <div class="flex shrink-0 items-center gap-2">
        {{ $slot }}
    </div>
</header>
