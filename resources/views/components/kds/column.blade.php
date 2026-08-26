@props(['status', 'label'])

{{-- KdsStatusColumn — sticky header with a live count; only the card list scrolls. --}}
<section class="kds-column border border-slate-200 bg-slate-100">
    <header class="pos-dock flex items-center justify-between border-b-2 px-3 py-2"
            :class="{
                new: 'border-slate-400 bg-white',
                accepted: 'border-sky-400 bg-sky-50',
                preparing: 'border-orange-400 bg-orange-50',
                ready: 'border-emerald-400 bg-emerald-50',
              }['{{ $status }}']">
        <span class="kds-card-title font-black uppercase tracking-[0.06em] text-slate-800">{{ $label }}</span>
        <span class="pos-num rounded-full bg-slate-900 px-2 py-0.5 text-[12px] font-black text-white"
              x-text="sortedFor('{{ $status }}').length"></span>
    </header>

    <div class="pos-scroll space-y-2 p-2">
        <template x-for="t in sortedFor('{{ $status }}')" :key="t.key || (t.orderId + '-' + t.kot)">
            <div><x-kds.kot-card ticket="t" /></div>
        </template>

        <div x-show="!sortedFor('{{ $status }}').length" class="py-10 text-center text-[12px] font-semibold text-slate-400">
            No tickets
        </div>
    </div>
</section>
