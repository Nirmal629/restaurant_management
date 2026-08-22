@props(['expr', 'size' => 'md'])

@php
    $pad = $size === 'sm' ? 'px-1 py-[1px] text-[9px]' : 'px-1.5 py-[1px] text-[9.5px]';
@endphp

{{-- TableStatusBadge — glyph + word + border; colour is reinforcement only. --}}
<span :class="statusClass({{ $expr }})"
      class="pos-num inline-flex shrink-0 items-center gap-1 rounded border {{ $pad }} font-bold uppercase leading-[14px] tracking-[0.05em]">
    <span class="text-[10px] leading-none no-underline" x-text="statusGlyph({{ $expr }})"></span>
    <span x-text="statusLabel({{ $expr }})"></span>
</span>
