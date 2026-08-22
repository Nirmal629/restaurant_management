@props(['ticket' => 't'])

{{--
    PriorityBadge — deliberately smaller and quieter than the delay chip in
    WaitTimeIndicator, per the brief ("do not visually overtake delay status").
    Hidden entirely for 'normal' so it never adds noise to the common case.
--}}
<span x-show="{{ $ticket }}.priority !== 'normal'"
      class="inline-flex items-center gap-1 rounded px-1.5 py-px text-[9px] font-black uppercase tracking-wide"
      :class="{
        priority: 'bg-violet-100 text-violet-800 border border-violet-300',
        rush: 'bg-rose-100 text-rose-800 border border-rose-300',
        vip: 'bg-amber-100 text-amber-900 border border-amber-400',
        waiting: 'bg-sky-100 text-sky-800 border border-sky-300',
      }[{{ $ticket }}.priority]"
      x-text="priorityLabel({{ $ticket }}.priority)"></span>
