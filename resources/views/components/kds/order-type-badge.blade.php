@props(['ticket' => 't'])

{{-- OrderTypeBadge — dine-in is the table number itself (strongest element); takeaway/delivery get a compact label. --}}
<span class="inline-flex items-center gap-1 rounded px-1.5 py-px text-[9px] font-black uppercase tracking-wide"
      :class="{
        dinein: 'bg-slate-800 text-white',
        takeaway: 'bg-indigo-100 text-indigo-800 border border-indigo-300',
        delivery: 'bg-teal-100 text-teal-800 border border-teal-300',
      }[{{ $ticket }}.orderType]">
    <template x-if="{{ $ticket }}.orderType === 'dinein'"><span>Dine In</span></template>
    <template x-if="{{ $ticket }}.orderType === 'takeaway'"><span>Takeaway</span></template>
    <template x-if="{{ $ticket }}.orderType === 'delivery'"><span>Delivery</span></template>
</span>
