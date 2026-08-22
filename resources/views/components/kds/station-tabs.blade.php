{{-- KitchenStationTabs — always visible tabs, never a dropdown, so switching stays one tap. --}}
<div class="pos-dock flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-3 py-2 pos-no-scrollbar">
    <template x-for="s in stations" :key="s.key">
        <button type="button" @click="activeStation = s.key"
                :class="activeStation === s.key ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                class="flex shrink-0 items-center gap-1.5 rounded-md border px-3 py-1.5">
            <span class="text-[12px] font-bold" x-text="s.label"></span>
            <span class="pos-num rounded px-1.5 py-px text-[10px] font-bold"
                  :class="activeStation === s.key ? 'bg-white/20' : 'bg-slate-100 text-slate-500'"
                  x-text="stationCount(s.key)"></span>
        </button>
    </template>
</div>
