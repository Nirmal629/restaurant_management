{{--
    KdsBoard — 4 columns, each independently scrollable. The board wrapper
    fills remaining height without its own vertical scrollbar (columns own
    that); it only scrolls horizontally, and only if the columns can't fit
    side by side (narrow viewports).
--}}
<div class="kds-board-wrap bg-slate-100 p-2">
    <div class="kds-board">
        <x-kds.column status="new" label="New" />
        <x-kds.column status="accepted" label="Accepted" />
        <x-kds.column status="preparing" label="Preparing" />
        <x-kds.column status="ready" label="Ready" />
    </div>
</div>
