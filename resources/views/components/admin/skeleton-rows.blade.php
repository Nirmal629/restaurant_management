@props(['cols' => 5, 'rows' => 6])

{{-- LoadingSkeleton — a handful of shimmering placeholder rows for the loading state demo. --}}
<div class="space-y-2 p-3">
    @for ($r = 0; $r < $rows; $r++)
        <div class="flex items-center gap-3 rounded-md border border-slate-100 p-2.5">
            @for ($c = 0; $c < $cols; $c++)
                <div class="adm-skeleton h-3 flex-1" style="max-width: {{ $c === 0 ? '160px' : '90px' }}"></div>
            @endfor
        </div>
    @endfor
</div>
