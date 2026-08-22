<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <title>Kitchen Display · Royal Bengal Restaurant</title>
    {{--
        pos.css supplies the shared "operational terminal" layout primitives
        (pos-shell, pos-header, pos-infobar, pos-scroll, pos-dock, pos-num, …)
        so the KDS matches the POS and Floor/Table screens exactly.
    --}}
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/kds.css', 'resources/js/kds.js'])
</head>

{{--
    ============================================================================
    KITCHEN DISPLAY SYSTEM
    ============================================================================
    Same viewport contract as POS/Floor: <body> is 100vh, never scrolls.

        KdsHeader             (pos-header, fixed ~56px)
        KitchenSummaryBar     (pos-infobar, fixed 38/44px, clickable filters)
        KitchenStationTabs    (dock)
        KdsFilterBar          (dock, hidden entirely in TV mode)
        KdsBoard | ExpeditorBoard   (the only thing that grows — each of its
                                     4 columns scrolls independently)

    TV mode adds .kds-tv to the root: bigger type, secondary chrome hidden,
    via the `kds-hide-secondary` utility — no separate template branch needed.
--}}
<body x-data="kdsApp" x-cloak
      class="kds-root pos-shell bg-slate-100 text-slate-900 antialiased"
      :class="tvMode && 'kds-tv'">

    <x-kds.header />
    <x-kds.summary-bar />
    <x-kds.station-tabs />
    <x-kds.filter-bar />

    <div class="pos-workspace">
        <template x-if="viewMode === 'kitchen'"><x-kds.board /></template>
        <template x-if="viewMode === 'expeditor'"><x-kds.expeditor-board /></template>
    </div>

    <x-kds.new-kot-notification />
    <x-kds.toast />

    <div x-ref="overlayRoot">
        <x-kds.overlays.detail-drawer />
        <x-kds.overlays.priority-modal />
        <x-kds.overlays.reprint-modal />
        <x-kds.overlays.unavailable-modal />
        <x-kds.overlays.history-drawer />
    </div>
</body>
</html>
