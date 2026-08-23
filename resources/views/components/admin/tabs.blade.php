@props(['tabs', 'active' => 'activeTab'])

{{-- Tabs — generic horizontal strip driven by an Alpine variable name (default `activeTab`). --}}
<div class="pos-dock adm-tabs border-b border-slate-200 bg-white px-4 py-2">
    @foreach ($tabs as $key => $label)
        <button type="button" @click="{{ $active }} = '{{ $key }}'"
                :class="{{ $active }} === '{{ $key }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                class="shrink-0 rounded-md border px-3 py-1.5 text-[11.5px] font-bold uppercase tracking-wide">{{ $label }}</button>
    @endforeach
</div>
