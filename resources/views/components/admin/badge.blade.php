@props(['tone' => 'slate', 'label' => null, 'expr' => null, 'classExpr' => null, 'labelExpr' => null])

@php
    $tones = [
        'slate'   => 'border-slate-300 bg-slate-100 text-slate-600',
        'sky'     => 'border-sky-300 bg-sky-50 text-sky-800',
        'emerald' => 'border-emerald-400 bg-emerald-50 text-emerald-800',
        'amber'   => 'border-amber-400 bg-amber-50 text-amber-800',
        'rose'    => 'border-rose-300 bg-rose-50 text-rose-700',
        'violet'  => 'border-violet-300 bg-violet-50 text-violet-800',
        'orange'  => 'border-orange-300 bg-orange-50 text-orange-800',
    ];
    $base = 'inline-flex shrink-0 items-center gap-1 rounded border px-1.5 py-[1px] text-[9.5px] font-black uppercase leading-[14px] tracking-[0.05em]';
@endphp

@if ($expr)
    {{-- Dynamic mode: {{ $expr }} is a row variable name; the caller's store must expose statusClass()/statusLabel() (or classExpr/labelExpr overrides). --}}
    <span {{ $attributes->merge(['class' => $base]) }}
          :class="{{ $classExpr ?? 'statusClass(' . $expr . ')' }}"
          x-text="{{ $labelExpr ?? 'statusLabel(' . $expr . ')' }}"></span>
@else
    {{-- Static mode --}}
    <span {{ $attributes->merge(['class' => $base . ' ' . ($tones[$tone] ?? $tones['slate'])]) }}>{{ $label }}</span>
@endif
