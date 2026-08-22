{{-- BillItemsPanel — header (dock) + scrollable item list. Only this list scrolls. --}}
<section class="bil-items-col border-r border-slate-200 bg-slate-50">
    <div class="pos-dock flex items-center gap-2 border-b border-slate-200 bg-white px-3 py-2">
        <h2 class="text-[11.5px] font-black uppercase tracking-[0.07em] text-slate-800">Bill Items</h2>
        <span class="pos-num rounded bg-slate-100 px-1.5 py-px text-[10px] font-bold text-slate-500" x-text="billableItems.length + ' items'"></span>
        <span class="flex-1"></span>
        <button type="button" @click="openSplit()"
                class="flex h-7 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2 text-[10.5px] font-bold text-slate-700 hover:border-slate-900">
            <x-pos.icon name="split" class="h-3.5 w-3.5" /> Split Bill
            <kbd class="hidden rounded bg-slate-100 px-1 text-[9px] text-slate-400 xl:inline">F6</kbd>
        </button>
    </div>

    <div class="pos-scroll space-y-1.5 p-2.5">
        <template x-for="i in items" :key="i.uid">
            <div><x-billing.item-row item="i" /></div>
        </template>

        <p x-show="!items.length" class="py-16 text-center text-[12.5px] font-semibold text-slate-400">No items on this bill.</p>
    </div>
</section>
