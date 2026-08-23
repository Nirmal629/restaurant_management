@props(['total'])

{{-- Pagination — pairs with paginationMixin()'s page/perPage/setPage/pageCount. --}}
<div class="pos-dock flex items-center gap-2 border-t border-slate-200 bg-white px-4 py-2">
    <p class="text-[11px] font-semibold text-slate-500">
        Showing <span class="pos-num font-bold text-slate-700" x-text="Math.min((page - 1) * perPage + 1, {{ $total }})"></span>–<span class="pos-num font-bold text-slate-700" x-text="Math.min(page * perPage, {{ $total }})"></span>
        of <span class="pos-num font-bold text-slate-700" x-text="{{ $total }}"></span>
    </p>
    <span class="flex-1"></span>
    <div class="flex items-center gap-1">
        <button type="button" @click="setPage(page - 1, {{ $total }})" :disabled="page <= 1"
                class="grid h-7 w-7 place-items-center rounded border border-slate-300 text-slate-500 hover:border-slate-900 disabled:cursor-not-allowed disabled:opacity-30">
            <x-pos.icon name="chevron-left" class="h-3.5 w-3.5" />
        </button>
        <span class="pos-num px-2 text-[11.5px] font-bold text-slate-700">
            <span x-text="page"></span> / <span x-text="pageCount({{ $total }})"></span>
        </span>
        <button type="button" @click="setPage(page + 1, {{ $total }})" :disabled="page >= pageCount({{ $total }})"
                class="grid h-7 w-7 place-items-center rounded border border-slate-300 text-slate-500 hover:border-slate-900 disabled:cursor-not-allowed disabled:opacity-30">
            <x-pos.icon name="chevron-right" class="h-3.5 w-3.5" />
        </button>
    </div>
</div>
