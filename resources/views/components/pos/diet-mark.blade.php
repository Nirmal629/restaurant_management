@props(['expr', 'size' => 'h-3.5 w-3.5'])

{{--
    FSSAI-style diet mark. Shape carries the meaning, not just hue:
    filled circle = veg, triangle = non-veg, hollow ring = egg.
--}}
<span
    :class="{ veg: 'border-emerald-600', nonveg: 'border-rose-600', egg: 'border-amber-500' }[{{ $expr }}]"
    :title="{ veg: 'Vegetarian', nonveg: 'Non-vegetarian', egg: 'Contains egg' }[{{ $expr }}]"
    class="{{ $size }} inline-grid shrink-0 place-items-center rounded-[2px] border-[1.5px]"
>
    <span x-show="{{ $expr }} === 'veg'" class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
    <span x-show="{{ $expr }} === 'nonveg'" class="pos-tri h-1.5 w-2 bg-rose-600"></span>
    <span x-show="{{ $expr }} === 'egg'" class="h-2 w-2 rounded-full border-[1.5px] border-amber-500"></span>
</span>
