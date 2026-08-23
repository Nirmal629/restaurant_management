@props(['idExpr'])

{{--
    ActionMenu (⋮) — generic per-row overflow menu. `idExpr` is a unique
    Alpine expression (e.g. a row id) so only one row's menu is open at a
    time via a single shared `openRowMenu` variable on the store.
--}}
<div class="relative" x-data @click.outside="openRowMenu === ({{ $idExpr }}) && (openRowMenu = null)">
    <button type="button" @click="openRowMenu = openRowMenu === ({{ $idExpr }}) ? null : ({{ $idExpr }})"
            class="grid h-7 w-7 place-items-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Row actions">
        <x-pos.icon name="dots" class="h-4 w-4" stroke="2.4" />
    </button>
    <div x-show="openRowMenu === ({{ $idExpr }})" x-cloak x-transition.origin.top.right
         class="absolute right-0 top-8 z-30 w-52 overflow-hidden rounded-md border border-slate-200 bg-white py-1 shadow-xl">
        {{ $slot }}
    </div>
</div>
