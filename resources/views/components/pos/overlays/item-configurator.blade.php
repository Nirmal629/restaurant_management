{{-- ItemConfiguratorModal — variants, modifiers, instructions, quantity --}}
<x-pos.dialog name="config" width="max-w-lg" title="Configure item">
    <template x-if="config">
        <div>
            {{-- Item identity + running price --}}
            <div class="flex items-start gap-2 border-b border-slate-200 bg-white px-4 py-3">
                <x-pos.diet-mark expr="config.item.diet" size="h-4 w-4 mt-0.5" />
                <div class="min-w-0 flex-1">
                    <p class="text-[15px] font-bold leading-tight text-slate-900" x-text="config.item.name"></p>
                    <p class="pos-num mt-0.5 text-[11.5px] font-semibold text-slate-500">
                        <span x-text="config.item.code"></span> ·
                        <span x-text="config.item.station"></span> ·
                        <span x-text="config.item.prep + ' min'"></span> ·
                        <span x-text="money(config.item.price) + ' base'"></span>
                    </p>
                </div>
                <span class="pos-num shrink-0 text-[17px] font-black text-slate-900" x-text="money(configUnitPrice)"></span>
            </div>

            <div class="space-y-3 p-4">
                {{-- Option groups --}}
                <template x-for="g in config.groups" :key="g.id">
                    <fieldset>
                        <legend class="mb-1.5 flex w-full items-center gap-1.5">
                            <span class="text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600" x-text="g.label"></span>
                            <span x-show="g.required" class="rounded bg-rose-100 px-1 text-[9px] font-bold uppercase tracking-wide text-rose-700">Required</span>
                            <span x-show="!g.required" class="rounded bg-slate-100 px-1 text-[9px] font-bold uppercase tracking-wide text-slate-500">Optional</span>
                            <span x-show="g.type === 'multi'" class="text-[10px] font-semibold text-slate-400">choose any</span>
                        </legend>

                        <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                            <template x-for="o in g.options" :key="o.id">
                                <button type="button" @click="configPick(g, o.id)"
                                        :class="configChecked(g, o.id)
                                            ? 'border-slate-900 bg-slate-900 text-white'
                                            : 'border-slate-300 bg-white text-slate-700 hover:border-slate-500'"
                                        class="flex h-10 items-center gap-2 rounded-md border px-2.5 text-left">
                                    <span :class="[g.type === 'multi' ? 'rounded-[3px]' : 'rounded-full', configChecked(g, o.id) ? 'border-white bg-white' : 'border-slate-400']"
                                          class="grid h-4 w-4 shrink-0 place-items-center border-2">
                                        <x-pos.icon name="check" class="h-2.5 w-2.5 text-slate-900" x-show="configChecked(g, o.id)" stroke="3.5" />
                                    </span>
                                    <span class="min-w-0 flex-1 truncate text-[12.5px] font-semibold" x-text="o.label"></span>
                                    <span x-show="o.delta" class="pos-num shrink-0 text-[11.5px] font-bold"
                                          :class="configChecked(g, o.id) ? 'text-white' : 'text-slate-500'"
                                          x-text="'+' + money(o.delta)"></span>
                                </button>
                            </template>
                        </div>
                    </fieldset>
                </template>

                {{-- Special instructions --}}
                <div>
                    <p class="mb-1.5 text-[10.5px] font-black uppercase tracking-[0.09em] text-slate-600">Special instructions</p>
                    <div class="mb-1.5 flex flex-wrap gap-1.5">
                        @foreach (['No Onion', 'No Garlic', 'Less Spicy', 'Extra Spicy', 'Jain', 'Serve Later', 'No Nuts'] as $preset)
                            <button type="button"
                                    @click="config.note = config.note.includes('{{ $preset }}') ? config.note.replace('{{ $preset }}', '').replace(/^[,\s]+|[,\s]+$/g, '') : (config.note ? config.note + ', ' : '') + '{{ $preset }}'"
                                    :class="config.note.includes('{{ $preset }}') ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:border-slate-500'"
                                    class="rounded-md border px-2 py-1 text-[11px] font-bold">{{ $preset }}</button>
                        @endforeach
                    </div>
                    <textarea x-model="config.note" rows="2" placeholder="Anything else for the kitchen…"
                              class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea>
                </div>
            </div>
        </div>
    </template>

    <x-slot:footer>
        <template x-if="config">
            <div class="flex items-center gap-3">
                <x-pos.qty-control dec="config.qty = Math.max(1, config.qty - 1)" inc="config.qty++" value="config.qty" />

                <div class="flex-1 leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-500">Line total</p>
                    <p class="pos-num text-[19px] font-black leading-none text-slate-900" x-text="money(configTotal)"></p>
                </div>

                <button type="button" @click="config = null; back()"
                        class="h-10 rounded-md border border-slate-300 bg-white px-4 text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
                <button type="button" @click="commitConfig()" :disabled="!configValid"
                        class="h-10 rounded-md bg-slate-900 px-5 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                        x-text="config.editingUid ? 'Update line' : 'Add to order'"></button>
            </div>
        </template>
    </x-slot:footer>
</x-pos.dialog>
