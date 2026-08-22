{{-- ServiceChargeRow — lockable; a cashier-level override is approval-gated. --}}
<div class="flex items-baseline justify-between text-[11.5px]">
    <dt class="flex items-center gap-1 font-medium text-slate-500">
        <span x-text="charges.serviceLabel"></span>
        <span class="pos-num text-slate-400" x-text="'(' + (charges.serviceRate * 100) + '%)'"></span>
        <button type="button" @click="requestServiceOverride()" class="ml-0.5 text-slate-400 hover:text-slate-700" :title="charges.serviceLocked ? 'Locked — manager approval required to remove' : 'Toggle service charge'">
            <template x-if="charges.serviceLocked"><span><x-pos.icon name="lock" class="h-3 w-3" /></span></template>
            <template x-if="!charges.serviceLocked"><span><x-pos.icon name="edit" class="h-3 w-3" /></span></template>
        </button>
    </dt>
    <dd class="pos-num font-semibold" :class="charges.serviceRemoved ? 'text-slate-400 line-through' : 'text-slate-800'" x-text="money(Math.round(taxableAmount * charges.serviceRate))"></dd>
</div>
