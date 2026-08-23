@props([
    'name',
    'title',
    'subtitle' => null,
    'width' => 'max-w-3xl',
    'variant' => 'modal', // modal | drawer
    'tone' => 'light',    // light | dark header
    'close' => 'back',     // back | all
])

@php
    $drawer = $variant === 'drawer';
    $shell = $drawer
        ? 'h-full w-full ' . $width . ' border-l rounded-none'
        : 'w-full ' . $width . ' max-h-[88vh] rounded-lg border';
    $headTone = $tone === 'dark'
        ? 'bg-slate-900 text-white border-slate-800'
        : 'bg-slate-50 text-slate-900 border-slate-200';
    $closeAction = $close === 'all' ? 'closeAll()' : 'back()';
    $isOpenExpression = "(typeof overlay !== 'undefined' && overlay === '{$name}') || (typeof stack !== 'undefined' && stack.includes('{$name}'))";
@endphp

<div
    x-show="{{ $isOpenExpression }}"
    x-cloak
    style="z-index: 60"
    class="fixed inset-0 flex {{ $drawer ? 'justify-end' : 'items-center justify-center p-4' }}"
    role="dialog"
    aria-modal="true"
>
    <div class="absolute inset-0 bg-slate-950/45" @click="({{ $isOpenExpression }}) && {{ $closeAction }}"></div>

    <div class="relative flex min-h-0 flex-col border-slate-300 bg-white shadow-2xl {{ $shell }}">
        <header class="flex shrink-0 items-center gap-3 border-b px-4 py-2.5 {{ $headTone }}">
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-[13px] font-bold uppercase tracking-[0.06em]">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="truncate text-[11px] {{ $tone === 'dark' ? 'text-slate-400' : 'text-slate-500' }}">{!! $subtitle !!}</p>
                @endif
            </div>

            {{ $headerActions ?? '' }}

            <button type="button" @click="{{ $closeAction }}"
                    class="grid h-7 w-7 shrink-0 place-items-center rounded {{ $tone === 'dark' ? 'text-slate-400 hover:bg-slate-800 hover:text-white' : 'text-slate-500 hover:bg-slate-200 hover:text-slate-900' }}"
                    aria-label="Close">
                <x-pos.icon name="x" class="h-4 w-4" stroke="2" />
            </button>
        </header>

        <div class="pos-scroll">
            {{ $slot }}
        </div>

        @isset($footer)
            <footer class="pos-dock border-t border-slate-200 bg-slate-50 px-4 py-3">
                {{ $footer }}
            </footer>
        @endisset
    </div>
</div>
