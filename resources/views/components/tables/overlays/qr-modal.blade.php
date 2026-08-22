{{-- TableQrModal — section 30. Speckle pattern is a placeholder, not a real code. --}}
<x-pos.dialog name="qr" width="max-w-xs" title="Table QR">
    <template x-if="table(activeTableId)">
        <div class="p-4 text-center">
            <p class="pos-num text-[16px] font-black text-slate-900" x-text="'Table ' + activeTableId"></p>

            <div class="mx-auto mt-3 h-36 w-36 rounded-md border-2 border-slate-300 bg-white p-2">
                <div class="flr-qr h-full w-full">
                    <template x-for="(on, i) in qrCells(activeTableId)" :key="i">
                        <span :class="on ? 'bg-slate-900' : 'bg-transparent'" class="aspect-square rounded-[1px]"></span>
                    </template>
                </div>
            </div>
            <p class="mt-1 text-[9.5px] font-semibold uppercase tracking-wide text-slate-400">Placeholder — not scannable</p>

            <div class="mt-3 rounded-md border border-slate-200 bg-slate-50 p-2 text-left">
                <p class="text-[9.5px] font-black uppercase tracking-[0.08em] text-slate-400">Public menu URL</p>
                <p class="pos-num truncate text-[11px] font-semibold text-slate-600" x-text="'menu.royalbengal.example/t/' + activeTableId.toLowerCase()"></p>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <div class="grid grid-cols-3 gap-1.5">
            <button type="button" @click="notify('QR sent to the counter printer')"
                    class="flex h-10 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">
                <x-pos.icon name="printer" class="h-4 w-4" /> Print
            </button>
            <button type="button" @click="notify('QR image queued for download')"
                    class="h-10 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-slate-700 hover:border-slate-900">Download</button>
            <button type="button" @click="notify('Token regenerated — old QR is now invalid')"
                    class="h-10 rounded-md border border-slate-300 bg-white text-[11px] font-bold text-rose-600 hover:border-rose-500">Regenerate</button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
