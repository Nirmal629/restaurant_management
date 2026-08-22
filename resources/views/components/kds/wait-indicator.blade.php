@props(['ticket' => 't', 'size' => 'md'])

@php
    $text = $size === 'lg' ? 'text-[22px]' : 'text-[15px]';
@endphp

{{-- WaitTimeIndicator — big minute count + explicit text label, never colour-only. --}}
<div class="flex items-center gap-1.5">
    <span class="pos-num {{ $text }} font-black leading-none"
          :class="{ normal: 'text-slate-900', warning: 'text-amber-600', critical: 'text-rose-600' }[waitLevel({{ $ticket }})]"
          x-text="waitMinutes({{ $ticket }}) + 'm'"></span>
    <span x-show="waitLevel({{ $ticket }}) !== 'normal'"
          class="rounded px-1 py-px text-[9px] font-black uppercase tracking-wide"
          :class="waitLevel({{ $ticket }}) === 'critical' ? 'bg-rose-600 text-white kds-pulse' : 'bg-amber-500 text-slate-950'"
          x-text="waitLabel({{ $ticket }})"></span>
</div>
