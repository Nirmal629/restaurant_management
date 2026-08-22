{{-- ManagerApprovalModal — PIN keypad, same pattern as POS. Requested/reason/detail are shown so the log reads clean. --}}
<x-pos.dialog name="approval" width="max-w-sm" title="Manager Approval" tone="dark">
    <div class="space-y-3 p-4">
        <div class="flex items-start gap-2.5">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-amber-100 text-amber-700">
                <x-pos.icon name="shield" class="h-4 w-4" />
            </span>
            <div>
                <p class="text-[13.5px] font-bold text-slate-900" x-text="approval.title"></p>
                <p class="mt-0.5 text-[12px] leading-snug text-slate-600" x-text="approval.detail"></p>
            </div>
        </div>

        <dl class="grid grid-cols-2 gap-2 rounded-md border border-slate-200 bg-slate-50 p-2.5 text-[11px]">
            <div><dt class="font-bold uppercase tracking-wide text-slate-400">Requested By</dt><dd class="font-semibold text-slate-800" x-text="operator.role + ' ' + operator.name"></dd></div>
            <div><dt class="font-bold uppercase tracking-wide text-slate-400">Date / Time</dt><dd class="pos-num font-semibold text-slate-800" x-text="clock"></dd></div>
        </dl>

        <div>
            <label class="mb-1.5 block text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Manager PIN</label>
            <input x-model="approval.pin" data-autofocus type="password" inputmode="numeric" maxlength="6" @keydown.enter="submitApproval()"
                   class="pos-num h-12 w-full rounded-md border border-slate-300 bg-white px-3 text-center text-[22px] font-black tracking-[0.4em] focus:border-slate-900 focus:outline-none"
                   placeholder="••••" />
            <p x-show="approval.error" class="mt-1 text-[11.5px] font-semibold text-rose-600" x-text="approval.error"></p>
        </div>

        <div class="grid grid-cols-3 gap-1.5">
            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $n)
                <button type="button" @click="approval.pin = (approval.pin + '{{ $n }}').slice(0, 6); approval.error = ''"
                        class="pos-num h-11 rounded-md border border-slate-300 bg-white text-[16px] font-bold text-slate-800 hover:border-slate-900 active:bg-slate-100">{{ $n }}</button>
            @endforeach
            <button type="button" @click="approval.pin = ''" class="h-11 rounded-md border border-slate-300 bg-white text-[11px] font-bold uppercase text-slate-600 hover:border-slate-900">Clear</button>
            <button type="button" @click="approval.pin = (approval.pin + '0').slice(0, 6); approval.error = ''"
                    class="pos-num h-11 rounded-md border border-slate-300 bg-white text-[16px] font-bold text-slate-800 hover:border-slate-900 active:bg-slate-100">0</button>
            <button type="button" @click="approval.pin = approval.pin.slice(0, -1)" class="h-11 rounded-md border border-slate-300 bg-white text-[11px] font-bold uppercase text-slate-600 hover:border-slate-900">Del</button>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="submitApproval()" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800">Approve</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
