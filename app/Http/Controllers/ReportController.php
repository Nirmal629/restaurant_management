<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\Invoice;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Refund;
use App\Models\StockLedgerEntry;
use App\Models\Supplier;
use App\Models\Wastage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports', ['reportsPayload' => $this->payload(request())]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->payload($request));
    }

    public function export(Request $request, string $kind)
    {
        abort_unless(in_array($kind, ['csv', 'excel', 'pdf'], true), 404);

        $payload = $this->payload($request);
        $filename = str($payload['report'])->slug()->append("-{$kind}.csv")->toString();
        $rows = $payload['generic']['rows'] ?: $payload['dailySales']['transactions'];

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Report', $payload['report']]);
        fputcsv($csv, ['From', $payload['dateFrom'], 'To', $payload['dateTo']]);
        if ($rows) {
            fputcsv($csv, array_keys((array) $rows[0]));
            foreach ($rows as $row) {
                fputcsv($csv, array_values((array) $row));
            }
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function payload(Request $request): array
    {
        [$from, $to] = $this->range($request);
        $report = $request->string('report', 'Daily Sales')->toString();

        return [
            'venue' => ['name' => config('app.name', 'Restaurant'), 'branch' => 'Ichapur Main Branch'],
            'categories' => $this->categories(),
            'report' => $report,
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
            'dailySales' => $this->dailySales($from, $to),
            'menuProfitability' => $this->menuProfitability($from, $to),
            'revenueSummary' => $this->revenueSummary($from, $to),
            'waiterSales' => $this->waiterSales($from, $to),
            'generic' => $this->genericReport($report, $from, $to),
        ];
    }

    private function range(Request $request): array
    {
        $from = Carbon::parse($request->input('from', now()->subDays(6)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        return $from->gt($to) ? [$to->copy()->startOfDay(), $from->copy()->endOfDay()] : [$from, $to];
    }

    private function invoiceQuery(Carbon $from, Carbon $to)
    {
        return Invoice::query()
            ->with(['order.waiter', 'order.table', 'order.customer', 'payments'])
            ->whereBetween('created_at', [$from, $to]);
    }

    private function dailySales(Carbon $from, Carbon $to): array
    {
        $invoices = $this->invoiceQuery($from, $to)->get();
        $gross = $invoices->sum(fn (Invoice $invoice) => $invoice->subtotal());
        $discount = $invoices->sum(fn (Invoice $invoice) => $invoice->totalDeductions());
        $net = $invoices->sum(fn (Invoice $invoice) => $invoice->grandTotal());
        $orders = $invoices->count();

        return [
            'kpis' => [
                'grossSales' => round($gross, 2),
                'discount' => round($discount, 2),
                'netSales' => round($net, 2),
                'orders' => $orders,
                'avgBill' => $orders ? round($net / $orders, 2) : 0,
                'guests' => $invoices->sum(fn (Invoice $invoice) => (int) ($invoice->order?->guests ?? 0)),
            ],
            'hourly' => collect(range(11, 23))->map(fn ($hour) => [
                'hour' => Carbon::createFromTime($hour)->format('g A'),
                'sales' => round($invoices->filter(fn (Invoice $invoice) => (int) $invoice->created_at->format('G') === $hour)->sum(fn (Invoice $invoice) => $invoice->grandTotal()), 2),
            ])->all(),
            'transactions' => $invoices->sortByDesc('created_at')->take(30)->map(fn (Invoice $invoice) => [
                'code' => $invoice->order?->code ?? $invoice->code,
                'time' => $invoice->created_at?->format('H:i'),
                'type' => $invoice->order?->type ?? 'Dine In',
                'waiter' => $invoice->order?->waiter?->name ?? '-',
                'amount' => $invoice->grandTotal(),
                'payment' => $invoice->payments->pluck('method')->unique()->map(fn ($m) => strtoupper($m))->implode(', ') ?: '-',
            ])->values()->all(),
        ];
    }

    private function menuProfitability(Carbon $from, Carbon $to): array
    {
        $rows = OrderItem::query()
            ->with('menuItem.category')
            ->whereHas('order', fn ($q) => $q->whereBetween('started_at', [$from, $to]))
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy('menu_item_id');

        return $rows->map(function ($items) {
            $item = $items->first()->menuItem;
            $revenue = $items->sum(fn (OrderItem $line) => $line->lineTotal());
            $qty = $items->sum('qty');
            $price = $qty ? round($revenue / $qty, 2) : (float) ($item?->base_price ?? 0);
            $foodCost = round($price * 0.35, 2);

            return [
                'item' => $item?->name ?? 'Unknown Item',
                'price' => $price,
                'foodCost' => $foodCost,
                'qtySold' => (int) $qty,
                'category' => $item?->category?->name ?? 'Uncategorised',
            ];
        })->sortByDesc('qtySold')->values()->all();
    }

    private function revenueSummary(Carbon $from, Carbon $to): array
    {
        $revenue = $this->invoiceQuery($from, $to)->get()->sum(fn (Invoice $invoice) => $invoice->grandTotal());
        $expenses = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])->whereIn('status', ['paid', 'approved'])->sum('amount');
        $cogs = round($revenue * 0.32, 2);

        return [
            'revenue' => round($revenue, 2),
            'cogs' => $cogs,
            'expenses' => round((float) $expenses, 2),
            'grossProfit' => round($revenue - $cogs - $expenses, 2),
            'months' => collect(range(5, 0))->map(function ($offset) {
                $start = now()->subMonths($offset)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $total = Invoice::whereBetween('created_at', [$start, $end])->get()->sum(fn (Invoice $invoice) => $invoice->grandTotal());

                return ['m' => $start->format('M'), 'revenue' => round($total, 2)];
            })->all(),
        ];
    }

    private function waiterSales(Carbon $from, Carbon $to): array
    {
        $orders = Order::with(['waiter', 'invoice', 'table'])
            ->whereBetween('started_at', [$from, $to])
            ->whereNotNull('waiter_id')
            ->get()
            ->groupBy('waiter_id');

        return $orders->map(function ($rows) {
            $sales = $rows->sum(fn (Order $order) => $order->invoice?->grandTotal() ?? 0);
            $orders = $rows->count();

            return [
                'name' => $rows->first()->waiter?->name ?? 'Unassigned',
                'orders' => $orders,
                'sales' => round($sales, 2),
                'avgBill' => $orders ? round($sales / $orders, 2) : 0,
                'tables' => $rows->pluck('table_id')->filter()->unique()->count(),
                'discounts' => round($rows->sum(fn (Order $order) => $order->invoice?->totalDeductions() ?? 0), 2),
                'voids' => $rows->where('status', 'cancelled')->count(),
            ];
        })->sortByDesc('sales')->values()->all();
    }

    private function genericReport(string $report, Carbon $from, Carbon $to): array
    {
        $rows = match ($report) {
            'Sales by Date' => $this->salesByDate($from, $to),
            'Sales by Hour' => $this->dailySales($from, $to)['hourly'],
            'Sales by Order Type' => $this->groupOrders($from, $to, 'type'),
            'Sales by Payment Method', 'Payment Summary', 'Cash vs Digital' => $this->payments($from, $to),
            'Sales by Table' => $this->salesByTable($from, $to),
            'Discount Report', 'Discounts' => $this->discounts($from, $to),
            'Cancellation/Void Report', 'Voids/Cancellations by Employee' => $this->voids($from, $to),
            'Tax Report' => $this->taxes($from, $to),
            'Item Sales', 'Top Selling Items', 'Least Selling Items' => $this->itemSales($from, $to, $report),
            'Category Sales' => $this->categorySales($from, $to),
            'Food Cost', 'Contribution Margin' => $this->menuProfitability($from, $to),
            'Current Stock', 'Low Stock', 'Inventory Valuation' => $this->stock($report),
            'Stock Movement', 'Consumption' => $this->stockMovement($from, $to),
            'Wastage' => $this->wastage($from, $to),
            'Purchase Summary', 'Supplier Purchases', 'Ingredient Purchase History' => $this->purchases($from, $to),
            'New Customers', 'Returning Customers', 'Top Customers', 'Customer Spend', 'Loyalty Usage' => $this->customers($from, $to, $report),
            'Revenue' => [],
            'Expenses' => $this->expenses($from, $to),
            'Refunds' => $this->refunds($from, $to),
            'Discounts by Employee' => $this->discountsByEmployee($from, $to),
            default => $this->fallback($from, $to, $report),
        };

        $rows = collect($rows)->map(function (array $row) {
            $row['label'] ??= $row['item'] ?? $row['name'] ?? $row['code'] ?? '-';
            $row['qty'] ??= $row['orders'] ?? $row['count'] ?? 0;
            $row['amount'] ??= $row['sales'] ?? $row['revenue'] ?? $row['value'] ?? 0;

            return $row;
        })->values()->all();

        $amount = collect($rows)->sum(fn ($row) => (float) ($row['amount'] ?? 0));
        $qty = collect($rows)->sum(fn ($row) => (float) ($row['qty'] ?? 0));

        return [
            'rows' => array_values($rows),
            'totals' => [
                'total' => round($amount, 2),
                'qty' => round($qty, 2),
                'avg' => count($rows) ? round($amount / max(1, count($rows)), 2) : 0,
                'best' => collect($rows)->sortByDesc(fn ($row) => (float) ($row['amount'] ?? $row['sales'] ?? $row['revenue'] ?? $row['value'] ?? 0))->first() ?? ['label' => '-'],
            ],
        ];
    }

    private function salesByDate(Carbon $from, Carbon $to): array
    {
        return $this->invoiceQuery($from, $to)->get()->groupBy(fn (Invoice $invoice) => $invoice->created_at->toDateString())
            ->map(fn ($rows, $date) => ['label' => $date, 'count' => $rows->count(), 'amount' => round($rows->sum(fn ($i) => $i->grandTotal()), 2)])
            ->values()->all();
    }

    private function groupOrders(Carbon $from, Carbon $to, string $column): array
    {
        return Order::whereBetween('started_at', [$from, $to])->get()->groupBy($column)
            ->map(fn ($rows, $label) => ['label' => $label ?: '-', 'orders' => $rows->count(), 'amount' => round($rows->sum(fn ($o) => $o->invoice?->grandTotal() ?? 0), 2)])
            ->values()->all();
    }

    private function payments(Carbon $from, Carbon $to): array
    {
        return Payment::whereBetween('paid_at', [$from, $to])->where('status', 'success')->get()->groupBy('method')
            ->map(fn ($rows, $method) => ['label' => strtoupper($method), 'count' => $rows->count(), 'amount' => round($rows->sum('amount'), 2)])
            ->values()->all();
    }

    private function itemSales(Carbon $from, Carbon $to, string $report): array
    {
        $rows = $this->menuProfitability($from, $to);
        $rows = $report === 'Least Selling Items'
            ? collect($rows)->sortBy('qtySold')
            : collect($rows)->sortByDesc('qtySold');

        return $rows->take(25)->map(fn ($row) => ['label' => $row['item'], 'qty' => $row['qtySold'], 'amount' => round($row['price'] * $row['qtySold'], 2), 'category' => $row['category']])->values()->all();
    }

    private function categorySales(Carbon $from, Carbon $to): array
    {
        return collect($this->itemSales($from, $to, 'Item Sales'))->groupBy('category')
            ->map(fn ($rows, $category) => ['label' => $category, 'qty' => $rows->sum('qty'), 'amount' => round($rows->sum('amount'), 2)])
            ->values()->all();
    }

    private function stock(string $report): array
    {
        return Ingredient::with('supplier')->get()
            ->filter(fn (Ingredient $i) => $report !== 'Low Stock' || $i->current_stock <= $i->min_stock)
            ->map(fn (Ingredient $i) => ['label' => $i->name, 'qty' => (float) $i->current_stock, 'unit' => $i->unit, 'value' => $i->stockValue(), 'status' => $i->stockStatus(), 'supplier' => $i->supplier?->name ?? '-'])
            ->values()->all();
    }

    private function stockMovement(Carbon $from, Carbon $to): array
    {
        return StockLedgerEntry::with('ingredient')->whereBetween('recorded_at', [$from, $to])->latest('recorded_at')->limit(50)->get()
            ->map(fn ($entry) => ['label' => $entry->ingredient?->name ?? '-', 'type' => $entry->type, 'qty' => (float) $entry->change_qty, 'amount' => round(abs($entry->change_qty) * ($entry->ingredient?->avg_cost ?? 0), 2), 'date' => $entry->recorded_at?->toDateString()])
            ->all();
    }

    private function wastage(Carbon $from, Carbon $to): array
    {
        return Wastage::with('ingredient')->whereBetween('date', [$from->toDateString(), $to->toDateString()])->get()
            ->map(fn ($w) => ['label' => $w->ingredient?->name ?? '-', 'qty' => (float) $w->qty, 'amount' => round($w->qty * ($w->ingredient?->avg_cost ?? 0), 2), 'reason' => $w->reason, 'date' => $w->date?->toDateString()])
            ->all();
    }

    private function purchases(Carbon $from, Carbon $to): array
    {
        return PurchaseOrder::with(['supplier', 'lines.ingredient'])->whereBetween('date', [$from->toDateString(), $to->toDateString()])->get()
            ->map(fn (PurchaseOrder $po) => ['label' => $po->code, 'supplier' => $po->supplier?->name ?? '-', 'count' => $po->lines->count(), 'amount' => $po->grandTotal(), 'status' => $po->status, 'date' => $po->date?->toDateString()])
            ->all();
    }

    private function customers(Carbon $from, Carbon $to, string $report): array
    {
        return Customer::with('orders.invoice')->get()->map(function (Customer $customer) {
            $sales = $customer->orders->sum(fn ($order) => $order->invoice?->grandTotal() ?? 0);
            return ['label' => $customer->name, 'phone' => $customer->phone, 'orders' => $customer->orders->count(), 'amount' => round($sales, 2), 'points' => $customer->loyalty_points ?? 0];
        })->sortByDesc($report === 'Top Customers' ? 'amount' : 'orders')->take(25)->values()->all();
    }

    private function expenses(Carbon $from, Carbon $to): array
    {
        return Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])->get()
            ->groupBy('category')
            ->map(fn ($rows, $category) => ['label' => $category, 'count' => $rows->count(), 'amount' => round($rows->sum('amount'), 2)])
            ->values()->all();
    }

    private function refunds(Carbon $from, Carbon $to): array
    {
        return Refund::whereBetween('refunded_at', [$from, $to])->get()
            ->map(fn ($r) => ['label' => $r->reason ?: 'Refund', 'amount' => (float) $r->amount, 'date' => $r->refunded_at?->toDateString()])
            ->all();
    }

    private function salesByTable(Carbon $from, Carbon $to): array
    {
        return Order::with(['table', 'invoice'])->whereBetween('started_at', [$from, $to])->whereNotNull('table_id')->get()->groupBy('table_id')
            ->map(fn ($rows) => ['label' => $rows->first()->table?->name ?? 'Table', 'orders' => $rows->count(), 'amount' => round($rows->sum(fn ($o) => $o->invoice?->grandTotal() ?? 0), 2)])
            ->values()->all();
    }

    private function discounts(Carbon $from, Carbon $to): array
    {
        return $this->invoiceQuery($from, $to)->get()
            ->map(fn (Invoice $i) => ['label' => $i->code, 'amount' => $i->totalDeductions(), 'billDiscount' => (float) $i->bill_discount_amount, 'coupon' => (float) $i->coupon_amount])
            ->filter(fn ($r) => $r['amount'] > 0)
            ->values()->all();
    }

    private function taxes(Carbon $from, Carbon $to): array
    {
        return $this->invoiceQuery($from, $to)->get()
            ->map(fn (Invoice $i) => ['label' => $i->code, 'taxable' => $i->taxableAmount(), 'cgst' => $i->cgstAmount(), 'sgst' => $i->sgstAmount(), 'amount' => $i->cgstAmount() + $i->sgstAmount()])
            ->values()->all();
    }

    private function voids(Carbon $from, Carbon $to): array
    {
        return Order::whereBetween('started_at', [$from, $to])->where('status', 'cancelled')->get()
            ->map(fn (Order $o) => ['label' => $o->code, 'type' => $o->type, 'amount' => $o->invoice?->grandTotal() ?? 0])
            ->all();
    }

    private function discountsByEmployee(Carbon $from, Carbon $to): array
    {
        return Employee::with(['orders.invoice'])->get()->map(fn (Employee $e) => [
            'label' => $e->name,
            'count' => $e->orders->count(),
            'amount' => round($e->orders->sum(fn ($o) => $o->invoice?->totalDeductions() ?? 0), 2),
        ])->filter(fn ($r) => $r['amount'] > 0)->values()->all();
    }

    private function fallback(Carbon $from, Carbon $to, string $report): array
    {
        return $this->salesByDate($from, $to) ?: [['label' => $report, 'count' => 0, 'amount' => 0]];
    }

    private function categories(): array
    {
        return [
            ['key' => 'sales', 'label' => 'Sales', 'reports' => ['Daily Sales', 'Sales by Date', 'Sales by Hour', 'Sales by Order Type', 'Sales by Payment Method', 'Sales by Waiter', 'Sales by Table', 'Discount Report', 'Cancellation/Void Report', 'Tax Report']],
            ['key' => 'menu', 'label' => 'Menu', 'reports' => ['Item Sales', 'Category Sales', 'Top Selling Items', 'Least Selling Items', 'Menu Profitability', 'Food Cost', 'Contribution Margin']],
            ['key' => 'kitchen', 'label' => 'Kitchen', 'reports' => ['Average Preparation Time', 'Delayed Orders', 'Station Performance', 'Item Preparation Time']],
            ['key' => 'inventory', 'label' => 'Inventory', 'reports' => ['Current Stock', 'Stock Movement', 'Consumption', 'Wastage', 'Low Stock', 'Inventory Valuation', 'Stock Variance']],
            ['key' => 'purchase', 'label' => 'Purchase', 'reports' => ['Purchase Summary', 'Supplier Purchases', 'Ingredient Purchase History']],
            ['key' => 'customer', 'label' => 'Customer', 'reports' => ['New Customers', 'Returning Customers', 'Top Customers', 'Customer Spend', 'Loyalty Usage']],
            ['key' => 'financial', 'label' => 'Financial', 'reports' => ['Revenue', 'Expenses', 'Gross Profit Estimate', 'Payment Summary', 'Cash vs Digital', 'Discounts', 'Refunds']],
            ['key' => 'employee', 'label' => 'Employee', 'reports' => ['Sales by Waiter', 'Orders by Waiter', 'Discounts by Employee', 'Voids/Cancellations by Employee']],
        ];
    }
}
