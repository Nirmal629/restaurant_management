<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    {{-- POS terminals are fixed-viewport appliances: no user zoom, no rubber-banding. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <title>POS · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/js/pos.js'])
</head>

{{--
    ============================================================================
    POS TERMINAL
    ============================================================================
    Viewport contract: <body> is exactly 100vh and never scrolls. The whole
    terminal is one flex column —

        PosHeader        (pos-header, fixed 46/52px)
        OrderInfoBar     (pos-infobar, fixed 38/44px)
        Workspace        (pos-workspace, flex:1 min-height:0)
          ├ CategorySidebar   pos-rail   → pos-scroll
          ├ Menu column       pos-menu   → toolbar (dock) + grid (pos-scroll)
          └ CurrentOrderPanel pos-cart   → header (dock) + list (pos-scroll)
                                           + OrderTotals (dock) + ActionBar (dock)

    Everything marked pos-dock is flex-shrink:0, which is what guarantees
    TOTAL / SEND KOT / BILL / PAY stay on screen at any cart length.
--}}
<body x-data="posApp" x-cloak
      @keydown.window="hotkey($event)"
      class="pos-root pos-shell bg-slate-100 text-slate-900 antialiased">

    <x-pos.header />
    <x-pos.order-info-bar />

    <div class="pos-workspace">
        <x-pos.category-sidebar />

        <main class="pos-menu">
            <x-pos.menu-toolbar />
            <x-pos.menu-item-grid />
        </main>

        <x-pos.current-order-panel />
    </div>

    <x-pos.notifications />

    {{-- Overlay layer: one stack, Esc pops one level ------------------- --}}
    <div x-ref="overlayRoot">
        <x-pos.overlays.table-selector />
        <x-pos.overlays.customer-selector />
        <x-pos.overlays.item-configurator />
        <x-pos.overlays.discount />
        <x-pos.overlays.approvals />
        <x-pos.overlays.bill-preview />
        <x-pos.overlays.payment />
        <x-pos.overlays.split-bill />
        <x-pos.overlays.running-orders />
        <x-pos.overlays.kitchen />
        <x-pos.overlays.misc />
    </div>
</body>
</html>
