{{-- TaxBreakdown — CGST/SGST when exclusive; a one-line note when prices already include tax. --}}
<div>
    <template x-if="charges.taxMode === 'exclusive'">
        <div class="space-y-1">
            <div class="flex items-baseline justify-between text-[11.5px]">
                <dt class="font-medium text-slate-500">CGST <span class="pos-num text-slate-400" x-text="'(' + (charges.cgstRate * 100) + '%)'"></span></dt>
                <dd class="pos-num font-semibold text-slate-800" x-text="money2(cgstAmount)"></dd>
            </div>
            <div class="flex items-baseline justify-between text-[11.5px]">
                <dt class="font-medium text-slate-500">SGST <span class="pos-num text-slate-400" x-text="'(' + (charges.sgstRate * 100) + '%)'"></span></dt>
                <dd class="pos-num font-semibold text-slate-800" x-text="money2(sgstAmount)"></dd>
            </div>
        </div>
    </template>
    <template x-if="charges.taxMode === 'inclusive'">
        <p class="text-[10.5px] font-medium italic text-slate-400">GST included in item prices above</p>
    </template>

    <button type="button" @click="charges.taxMode = charges.taxMode === 'exclusive' ? 'inclusive' : 'exclusive'"
            class="mt-1 text-[10px] font-bold text-slate-400 underline decoration-slate-300 underline-offset-2 hover:text-slate-700">
        Switch to <span x-text="charges.taxMode === 'exclusive' ? 'tax-inclusive' : 'tax-exclusive'"></span> pricing
    </button>
</div>
