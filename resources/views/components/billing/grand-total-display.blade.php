@props(['size' => 'lg'])

@php
    $text = $size === 'lg' ? 'text-[26px]' : 'text-[20px]';
@endphp

{{-- GrandTotalDisplay — the single strongest number on the whole screen. --}}
<div class="flex items-baseline justify-between border-t-2 border-slate-900 pt-2">
    <span class="text-[12px] font-black uppercase tracking-[0.08em] text-slate-900">Grand Total</span>
    <span class="pos-num {{ $text }} font-black leading-none tracking-tight text-slate-900" x-text="money(grandTotal)"></span>
</div>
