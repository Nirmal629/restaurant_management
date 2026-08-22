<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <title>Billing · Royal Bengal Restaurant</title>
    {{--
        pos.css supplies the shared "operational terminal" layout primitives
        (pos-shell, pos-header, pos-infobar, pos-scroll, pos-dock, pos-num, …)
        so Billing matches POS/Floor/KDS exactly.
    --}}
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/billing.css', 'resources/js/billing.js'])
</head>

{{--
    ============================================================================
    BILLING, PAYMENT & SPLIT BILL
    ============================================================================
    Same viewport contract as POS/Floor/KDS: <body> is 100vh, never scrolls.

        BillingHeader      (pos-header, fixed ~52px)
        BillingOrderInfo   (pos-infobar, fixed 38/44px)
        Workspace          (bil-workspace, flex:1 min-height:0)
          ├ BillItemsPanel   (bil-items-col)  → header dock + pos-scroll item list
          ├ BillSummary      (bil-summary-col) → header dock + pos-scroll body
          └ PaymentPanel     (bil-payment-col) → recap dock + pos-scroll body
                                                  + MixedPaymentRows + CompletePaymentButton (dock)

    Below 1280px, BillSummary/PaymentPanel collapse into tabs (bil-tab-active)
    so BillItemsPanel gets the full width — Grand Total/Paid/Due/Complete
    Payment stay reachable behind one tap either way.
--}}
<body x-data="billingApp('{{ route('pos') }}', '{{ route('tables') }}')" x-cloak
      @keydown.window="hotkey($event)"
      class="bil-root pos-shell bg-slate-100 text-slate-900 antialiased">

    <x-billing.header />
    <x-billing.order-info />

    <div class="pos-workspace bil-workspace" x-data="{ tab: 'summary' }">
        <x-billing.items-panel />

        {{-- Mobile/1024px tab switcher between Summary and Payment --}}
        <div class="pos-dock hidden w-full border-b border-slate-200 bg-white max-[1279px]:flex">
            <button type="button" @click="tab = 'summary'" :class="tab === 'summary' ? 'border-b-2 border-slate-900 text-slate-900' : 'text-slate-400'" class="flex-1 py-2 text-[11.5px] font-bold uppercase tracking-wide">Summary</button>
            <button type="button" @click="tab = 'payment'" :class="tab === 'payment' ? 'border-b-2 border-slate-900 text-slate-900' : 'text-slate-400'" class="flex-1 py-2 text-[11.5px] font-bold uppercase tracking-wide">Payment</button>
        </div>

        <div :class="tab === 'summary' && 'bil-tab-active'"><x-billing.summary /></div>
        <div :class="tab === 'payment' && 'bil-tab-active'"><x-billing.payment-panel /></div>
    </div>

    <x-billing.toast />

    <div x-ref="overlayRoot">
        <x-billing.overlays.discount-modal />
        <x-billing.overlays.approval-modal />
        <x-billing.overlays.complimentary-modal />
        <x-billing.overlays.cancel-item-modal />
        <x-billing.overlays.split-bill-modal />
        <x-billing.overlays.bill-preview-drawer />
        <x-billing.overlays.customer-drawer />
        <x-billing.overlays.gst-invoice-form />
        <x-billing.overlays.loyalty-modal />
        <x-billing.overlays.coupon-modal />
        <x-billing.overlays.payment-success-modal />
        <x-billing.overlays.payment-history-drawer />
        <x-billing.overlays.refund-modal />
        <x-billing.overlays.void-modal />
        <x-billing.overlays.close-table-modal />
        <x-billing.overlays.audit-modal />
    </div>
</body>
</html>
