{{-- PaymentMethodSelector — large, touch-friendly buttons. Card variants combine under one control. --}}
<div class="grid grid-cols-3 gap-1.5">
    @foreach ([
        ['cash', 'Cash', 'cash'],
        ['upi', 'UPI', 'qr'],
        ['card', 'Card', 'card'],
        ['wallet', 'Wallet', 'phone'],
        ['bank', 'Bank Transfer', 'receipt'],
        ['other', 'Other', 'dots'],
    ] as [$key, $label, $icon])
        <button type="button" @click="selectMethod('{{ $key === 'card' ? 'credit' : $key }}')"
                :class="(payDraft.method === '{{ $key }}' || ('{{ $key }}' === 'card' && ['credit','debit'].includes(payDraft.method)))
                    ? 'border-slate-900 bg-slate-900 text-white'
                    : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                class="flex h-14 flex-col items-center justify-center gap-1 rounded-md border text-[11px] font-bold uppercase tracking-wide">
            <x-pos.icon name="{{ $icon }}" class="h-4 w-4" />
            {{ $label }}
        </button>
    @endforeach
</div>
