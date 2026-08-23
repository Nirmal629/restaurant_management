@props(['icon' => 'search', 'title' => 'Nothing here yet', 'hint' => null])

{{--
    EmptyState — used for zero-results, cleared filters, and genuinely empty
    lists alike. $attributes is merged onto the root so callers can pass
    x-show/x-if directly on the <x-admin.empty-state> tag.
--}}
<div {{ $attributes->merge(['class' => 'flex flex-1 flex-col items-center justify-center gap-2 py-16 text-center']) }}>
    <x-pos.icon :name="$icon" class="h-8 w-8 text-slate-300" />
    <p class="text-[13px] font-bold text-slate-600">{{ $title }}</p>
    @if ($hint)
        <p class="max-w-[280px] text-[11.5px] text-slate-400">{{ $hint }}</p>
    @endif
    @isset($action)
        <div class="mt-1">{{ $action }}</div>
    @endisset
</div>
