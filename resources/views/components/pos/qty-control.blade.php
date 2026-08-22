@props(['dec', 'inc', 'value', 'size' => 'md'])

@php
    // 32px is the smallest reliable touch target on a restaurant tablet.
    $btn = $size === 'sm' ? 'h-7 w-7' : 'h-8 w-8';
    $val = $size === 'sm' ? 'w-8 text-[13px]' : 'w-10 text-sm';
@endphp

<div class="inline-flex shrink-0 items-center rounded-md border border-slate-300 bg-white">
    <button type="button" @click.stop="{{ $dec }}"
            class="{{ $btn }} grid place-items-center rounded-l-md text-slate-600 hover:bg-slate-100 active:bg-slate-200"
            aria-label="Decrease quantity">
        <x-pos.icon name="minus" class="h-3.5 w-3.5" stroke="2.4" />
    </button>
    <span class="{{ $val }} pos-num border-x border-slate-200 py-1 text-center font-bold text-slate-900"
          x-text="{{ $value }}"></span>
    <button type="button" @click.stop="{{ $inc }}"
            class="{{ $btn }} grid place-items-center rounded-r-md text-slate-600 hover:bg-slate-100 active:bg-slate-200"
            aria-label="Increase quantity">
        <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" />
    </button>
</div>
