<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Floor &amp; Tables · Royal Bengal Restaurant</title>
    {{--
        pos.css supplies the shared "operational terminal" layout primitives
        (pos-shell, pos-header, pos-scroll, pos-dock, pos-num, …) so this page
        matches the POS screen's design language pixel-for-pixel.
    --}}
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/tables.css', 'resources/js/tables.js'])
    <script>
        window.tablesModule = @json($tablesPayload);
        window.tablesRoutes = {
            data: @json(route('tables.data')),
            store: @json(route('tables.store')),
            floors: @json(route('tables.floors.store')),
            base: @json(url('/tables')),
            pos: @json(route('pos')),
            orders: @json(route('orders')),
            posOrders: @json(url('/pos/orders')),
        };
        window.realtimeVersionsUrl = @json(route('realtime.versions'));
        window.realtimeStreamUrl = @json(route('realtime.stream'));
    </script>
</head>

{{--
    ============================================================================
    FLOOR & TABLE MANAGEMENT
    ============================================================================
    Same viewport contract as the POS terminal: <body> is 100vh, never scrolls.

        PageHeader           (pos-header, fixed 46/52px)
        TableStatusSummary   (pos-infobar, fixed 38/44px, clickable filters)
        Workspace            (pos-workspace flr-workspace, flex:1 min-height:0)
          ├ FloorSidebar / FloorTabs  (flr-rail-w, collapses to tabs <1200px)
          └ Menu column
              ├ FloorLayoutEditor toolbar (dock, shown only in edit mode)
              ├ TableFilters              (dock)
              ├ TableMap                  (pos-scroll, the only thing that grows)
              └ Utilization footer        (dock)
--}}
<body x-data="tablesApp('{{ route('pos') }}')" x-cloak
      class="flr-root pos-shell bg-slate-100 text-slate-900 antialiased">

    <x-tables.header />
    <x-tables.status-summary />

    <div class="pos-workspace flr-workspace">
        <x-tables.floor-sidebar />

        <main class="pos-menu">
            <x-tables.filters />
            <x-tables.floor-map />
        </main>
    </div>

    <x-tables.toast />

    <div x-ref="overlayRoot">
        <x-tables.overlays.quick-actions />
        <x-tables.overlays.context-menu />
        <x-tables.overlays.start-modal />
        <x-tables.overlays.reserve-modal />
        <x-tables.overlays.details-drawer />
        <x-tables.overlays.reservation-drawer />
        <x-tables.overlays.billing-modal />
        <x-tables.overlays.cleaning-modal />
        <x-tables.overlays.disabled-modal />
        <x-tables.overlays.transfer-modal />
        <x-tables.overlays.merge-modal />
        <x-tables.overlays.unmerge-modal />
        <x-tables.overlays.waiter-modal />
        <x-tables.overlays.find-table-modal />
        <x-tables.overlays.add-table-modal />
        <x-tables.overlays.edit-table-modal />
        <x-tables.overlays.add-floor-modal />
        <x-tables.overlays.qr-modal />
        <x-tables.overlays.ready-popover />
    </div>
</body>
</html>
