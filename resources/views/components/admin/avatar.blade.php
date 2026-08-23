@props(['initialsExpr', 'size' => 'md'])

@php
    $dim = $size === 'sm' ? 'h-7 w-7 text-[10px]' : ($size === 'lg' ? 'h-11 w-11 text-[14px]' : 'h-8 w-8 text-[11px]');
@endphp

{{-- UserAvatar — initials circle, used for employees/customers/waiters throughout the admin modules. --}}
<span {{ $attributes->merge(['class' => "grid $dim shrink-0 place-items-center rounded-full bg-slate-200 font-bold text-slate-700"]) }}
      x-text="{{ $initialsExpr }}"></span>
