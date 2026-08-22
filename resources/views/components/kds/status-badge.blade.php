@props(['expr', 'size' => 'md'])

@php
    $pad = $size === 'sm' ? 'px-1 py-[1px] text-[9px]' : 'px-1.5 py-[1px] text-[10px]';
@endphp

{{-- KitchenStatusBadge — glyph + word + border; colour reinforces, never carries the meaning alone. --}}
<span :class="{
        new: 'border-slate-400 bg-slate-100 text-slate-700',
        accepted: 'border-sky-400 bg-sky-50 text-sky-800',
        preparing: 'border-orange-400 bg-orange-50 text-orange-800',
        ready: 'border-emerald-400 bg-emerald-50 text-emerald-800',
        picked_up: 'border-slate-300 bg-slate-100 text-slate-500',
      }[{{ $expr }}]"
      class="pos-num inline-flex shrink-0 items-center gap-1 rounded border {{ $pad }} font-black uppercase leading-[14px] tracking-[0.06em]">
    <span x-text="{
        new: '＋', accepted: '✓', preparing: '◑', ready: '●', picked_up: '✓✓',
      }[{{ $expr }}] || '•'"></span>
    <span x-text="{
        new: 'New', accepted: 'Accepted', preparing: 'Preparing', ready: 'Ready', picked_up: 'Picked Up',
      }[{{ $expr }}] || {{ $expr }}"></span>
</span>
