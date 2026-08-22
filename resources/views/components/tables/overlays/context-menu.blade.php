{{-- TableQuickActions — right-click / long-press context menu, section 13. --}}
<x-pos.dialog name="context" width="max-w-xs" title="Quick actions" :subtitle="null">
    <template x-if="activeCard">
        <div>
            <div class="flex items-center gap-2 border-b border-slate-200 bg-white px-4 py-2.5">
                <span class="pos-num text-[15px] font-black text-slate-900" x-text="activeCard.label"></span>
                <x-tables.status-badge expr="activeCard.status" size="sm" />
            </div>
            <div class="p-1.5">
                <template x-for="a in contextActions(activeCard)" :key="a.key">
                    <button type="button" @click="runContextAction(a.key)"
                            :class="a.danger ? 'text-rose-600 hover:bg-rose-50' : 'text-slate-700 hover:bg-slate-100'"
                            class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-[12.5px] font-semibold">
                        <span class="flex-1" x-text="a.label"></span>
                        <x-pos.icon name="chevron-right" class="h-3.5 w-3.5 text-slate-300" />
                    </button>
                </template>
            </div>
        </div>
    </template>
</x-pos.dialog>
