<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/settings.js'])
    <script>
        window.settingsModule = @json($settingsPayload);
        window.settingsRoutes = {
            data: @json(route('settings.data')),
            section: @json(url('/settings')),
        };
    </script>
</head>
<body x-data="settingsApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="settings" />

    <div class="adm-main">
        <x-admin.page-header title="Settings" subtitle="Ichapur Main Branch">
            <span x-show="isDirty" class="rounded border border-amber-400 bg-amber-50 px-2 py-1 text-[10.5px] font-bold uppercase tracking-wide text-amber-800">Unsaved changes</span>
        </x-admin.page-header>

        <div class="pos-workspace">
            {{-- Section nav --}}
            <aside class="w-56 shrink-0 border-r border-slate-200 bg-white">
                <nav class="pos-scroll p-2">
                    <template x-for="s in sections" :key="s.key">
                        <button type="button" @click="goToSection(s.key)"
                                :class="activeSection === s.key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                class="mb-0.5 flex w-full items-center gap-1.5 rounded-md px-2.5 py-1.5 text-left text-[11.5px] font-semibold">
                            <span class="min-w-0 flex-1 truncate" x-text="s.label"></span>
                        </button>
                    </template>
                </nav>
            </aside>

            {{-- Section form --}}
            <section class="flex min-w-0 flex-1 flex-col overflow-hidden">
                <div class="pos-dock flex items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
                    <h2 class="text-[13px] font-black text-slate-900" x-text="sections.find(s => s.key === activeSection)?.label"></h2>
                </div>

                <div class="pos-scroll bg-slate-100 p-3">
                    {{-- Numbering gets a bespoke layout (format + live example); everything else is generic. --}}
                    <template x-if="activeSection === 'numbering'">
                        <div class="grid gap-2.5" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                            <template x-for="[key, val] in Object.entries(settings.numbering)" :key="key">
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600" x-text="label(key)"></label>
                                    <input x-model="settings.numbering[key]" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                                    <p class="pos-num mt-1.5 text-[10.5px] font-semibold text-slate-400">Example: <span class="font-bold text-slate-700" x-text="numberingExamples[key]"></span></p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="activeSection !== 'numbering'">
                        <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));">
                            <template x-for="[key, val] in Object.entries(settings[activeSection])" :key="key">
                                <div :class="isLongField(key) && 'col-span-full'">
                                    <template x-if="fieldType(val) === 'boolean'">
                                        <label class="flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-3">
                                            <input type="checkbox" x-model="settings[activeSection][key]" class="h-4 w-4 accent-slate-900">
                                            <span class="text-[12px] font-semibold text-slate-700" x-text="label(key)"></span>
                                        </label>
                                    </template>
                                    <template x-if="fieldType(val) === 'number'">
                                        <div>
                                            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600" x-text="label(key)"></label>
                                            <input x-model.number="settings[activeSection][key]" type="number" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                                        </div>
                                    </template>
                                    <template x-if="fieldType(val) === 'text' && !isLongField(key)">
                                        <div>
                                            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600" x-text="label(key)"></label>
                                            <input x-model="settings[activeSection][key]" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" />
                                        </div>
                                    </template>
                                    <template x-if="fieldType(val) === 'text' && isLongField(key)">
                                        <div>
                                            <label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600" x-text="label(key)"></label>
                                            <textarea x-model="settings[activeSection][key]" rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <p class="mt-4 rounded border border-slate-200 bg-white px-3 py-2 text-[11px] leading-snug text-slate-400">
                        Settings are saved section-by-section and take effect for modules as they read the shared configuration.
                    </p>
                </div>

                {{-- Save / Reset dock --}}
                <div class="pos-dock flex items-center gap-2 border-t border-slate-200 bg-white px-4 py-2.5">
                    <span x-show="sectionDirty" class="text-[11px] font-semibold text-amber-700">You have unsaved changes in this section.</span>
                    <span class="flex-1"></span>
                    <button type="button" @click="resetSection()" :disabled="saving" :aria-busy="saving ? 'true' : 'false'" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-[11.5px] font-bold text-slate-700 hover:border-slate-900 disabled:cursor-not-allowed disabled:opacity-40">Reset</button>
                    <button type="button" @click="saveChanges()" :disabled="!sectionDirty || saving" :aria-busy="saving ? 'true' : 'false'" class="h-9 rounded-md bg-slate-900 px-4 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">Save Changes</button>
                </div>
            </section>
        </div>
    </div>

    <x-admin.toast />

    {{-- Unsaved-changes guard when switching sections --}}
    <div x-show="pendingSection" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/45" @click="cancelSwitch()"></div>
        <div class="relative w-full max-w-sm rounded-lg border border-slate-300 bg-white p-4 shadow-2xl">
            <p class="text-[13px] font-bold text-slate-900">Leave without saving?</p>
            <p class="mt-1 text-[11.5px] text-slate-500">You have unsaved changes in this section. They'll be lost if you switch without saving.</p>
            <div class="mt-3 flex gap-2">
                <button type="button" @click="discardAndSwitch()" class="h-9 flex-1 rounded-md border border-rose-300 bg-white text-[11.5px] font-bold text-rose-600 hover:bg-rose-50">Discard</button>
                <button type="button" @click="cancelSwitch()" class="h-9 flex-1 rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Cancel</button>
                <button type="button" @click="saveAndSwitch()" class="h-9 flex-1 rounded-md bg-slate-900 text-[11.5px] font-bold text-white hover:bg-slate-800">Save</button>
            </div>
        </div>
    </div>
</body>
</html>
