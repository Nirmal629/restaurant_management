@props(['text'])

{{-- AllergyAlert — the strongest visual treatment on the board, deliberately louder than a normal instruction block. --}}
<div class="mt-1 flex items-center gap-1.5 rounded border-2 border-rose-600 bg-rose-600 px-2 py-1">
    <x-pos.icon name="alert" class="h-3.5 w-3.5 shrink-0 text-white" stroke="2.2" />
    <span class="kds-card-text font-black uppercase tracking-wide text-white" x-text="{{ $text }}"></span>
</div>
