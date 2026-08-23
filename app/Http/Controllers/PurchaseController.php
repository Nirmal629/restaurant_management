<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\StockLedgerEntry;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases', ['purchaseModule' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function storeOrder(Request $request): JsonResponse
    {
        $data = $this->validatedOrder($request);

        $order = DB::transaction(function () use ($data) {
            $supplier = Supplier::firstOrCreate(['name' => $data['supplier']], ['status' => 'active']);
            $order = PurchaseOrder::create([
                'code' => $this->nextCode(PurchaseOrder::class, 'PO'),
                'supplier_id' => $supplier->id,
                'date' => now()->toDateString(),
                'expected_delivery' => $data['expectedDelivery'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'discount' => $data['discount'] ?? 0,
                'other_charges' => $data['otherCharges'] ?? 0,
                'created_by' => auth()->user()?->employee?->id,
            ]);

            foreach ($data['items'] as $line) {
                $ingredient = Ingredient::where('name', $line['ingredient'])->firstOrFail();
                $order->lines()->create([
                    'ingredient_id' => $ingredient->id,
                    'current_stock_snapshot' => $ingredient->current_stock,
                    'qty' => $line['qty'],
                    'unit' => $line['unit'] ?? $ingredient->unit,
                    'rate' => $line['rate'] ?? 0,
                    'tax_pct' => $line['tax'] ?? 0,
                ]);
            }

            return $order;
        });

        return response()->json(['order' => $this->orderResource($order->fresh(['supplier', 'lines.ingredient', 'creator', 'approver'])), 'message' => "{$order->code} saved"], 201);
    }

    public function status(Request $request, PurchaseOrder $order): JsonResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approval_pending', 'approved', 'ordered', 'cancelled'])]]);
        $order->update([
            'status' => $data['status'],
            'approved_by' => $data['status'] === 'approved' ? auth()->user()?->employee?->id : $order->approved_by,
        ]);

        return response()->json(['order' => $this->orderResource($order->fresh(['supplier', 'lines.ingredient', 'creator', 'approver'])), 'message' => "{$order->code} updated"]);
    }

    public function destroyOrder(PurchaseOrder $order): JsonResponse
    {
        if ($order->goodsReceipts()->exists()) {
            return response()->json(['message' => 'Delete linked goods receipts before deleting this purchase order.'], 422);
        }

        $code = $order->code;
        $order->delete();

        return response()->json(['orders' => $this->orders(), 'message' => "{$code} deleted"]);
    }

    public function storeReceipt(Request $request): JsonResponse
    {
        $data = $request->validate([
            'poRef' => ['required', 'string', 'exists:purchase_orders,code'],
            'invoiceNumber' => ['required', 'string', 'max:100'],
            'receivedDate' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient' => ['required', 'string', 'exists:ingredients,name'],
            'items.*.ordered' => ['required', 'numeric', 'min:0'],
            'items.*.prevReceived' => ['nullable', 'numeric', 'min:0'],
            'items.*.receivedNow' => ['required', 'numeric', 'min:0'],
            'items.*.rejected' => ['nullable', 'numeric', 'min:0'],
        ]);

        $receipt = DB::transaction(function () use ($data) {
            $order = PurchaseOrder::where('code', $data['poRef'])->firstOrFail();
            $receipt = GoodsReceipt::create([
                'code' => $this->nextCode(GoodsReceipt::class, 'GRN'),
                'purchase_order_id' => $order->id,
                'invoice_number' => $data['invoiceNumber'],
                'received_date' => $data['receivedDate'],
            ]);

            foreach ($data['items'] as $line) {
                $ingredient = Ingredient::where('name', $line['ingredient'])->firstOrFail();
                $accepted = max(0, (float) $line['receivedNow'] - (float) ($line['rejected'] ?? 0));
                $previous = (float) $ingredient->current_stock;

                $receipt->lines()->create([
                    'ingredient_id' => $ingredient->id,
                    'ordered_qty' => $line['ordered'],
                    'previously_received_qty' => $line['prevReceived'] ?? 0,
                    'received_now_qty' => $line['receivedNow'],
                    'rejected_qty' => $line['rejected'] ?? 0,
                ]);

                if ($accepted > 0) {
                    $ingredient->update(['current_stock' => $previous + $accepted]);
                    StockLedgerEntry::create([
                        'ingredient_id' => $ingredient->id,
                        'type' => 'PURCHASE',
                        'reference' => $receipt->code,
                        'previous_qty' => $previous,
                        'change_qty' => $accepted,
                        'new_qty' => $previous + $accepted,
                        'employee_id' => auth()->user()?->employee?->id,
                        'recorded_at' => now(),
                    ]);
                }
            }

            $fullyReceived = collect($data['items'])->every(fn ($line) => ((float) ($line['prevReceived'] ?? 0) + (float) $line['receivedNow']) >= (float) $line['ordered']);
            $order->update(['status' => $fullyReceived ? 'received' : 'partially_received']);

            return $receipt;
        });

        return response()->json([
            'receipt' => $this->receiptResource($receipt->fresh(['purchaseOrder.supplier', 'lines.ingredient'])),
            'orders' => $this->orders(),
            'message' => "{$receipt->code} recorded and inventory updated",
        ], 201);
    }

    public function destroyReceipt(GoodsReceipt $receipt): JsonResponse
    {
        $code = $receipt->code;

        DB::transaction(function () use ($receipt) {
            $receipt->load(['purchaseOrder', 'lines.ingredient']);

            foreach ($receipt->lines as $line) {
                $accepted = max(0, (float) $line->received_now_qty - (float) $line->rejected_qty);
                if ($accepted <= 0 || ! $line->ingredient) continue;

                $previous = (float) $line->ingredient->current_stock;
                $new = max(0, $previous - $accepted);
                $line->ingredient->update(['current_stock' => $new]);
                StockLedgerEntry::create([
                    'ingredient_id' => $line->ingredient_id,
                    'type' => 'ADJUST',
                    'reference' => "Reverse {$receipt->code}",
                    'previous_qty' => $previous,
                    'change_qty' => -$accepted,
                    'new_qty' => $new,
                    'employee_id' => auth()->user()?->employee?->id,
                    'recorded_at' => now(),
                ]);
            }

            $order = $receipt->purchaseOrder;
            $receipt->delete();
            if ($order) $this->refreshOrderReceiptStatus($order);
        });

        return response()->json(['receipts' => $this->receipts(), 'orders' => $this->orders(), 'message' => "{$code} deleted"]);
    }

    public function export()
    {
        $rows = collect([['PO Number', 'Supplier', 'Date', 'Expected Delivery', 'Status', 'Items', 'Total']])
            ->concat(PurchaseOrder::with(['supplier', 'lines'])->latest('date')->get()->map(fn ($o) => [
                $o->code,
                $o->supplier?->name,
                $o->date?->toDateString(),
                $o->expected_delivery?->toDateString(),
                $o->status,
                $o->lines->count(),
                $o->grandTotal(),
            ]));
        $csv = $rows->map(fn ($row) => collect($row)->map(fn ($cell) => '"' . str_replace('"', '""', (string) $cell) . '"')->implode(','))->implode("\n");

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="purchases.csv"']);
    }

    private function payload(): array
    {
        return [
            'venue' => ['name' => config('app.name'), 'branch' => 'Main Branch'],
            'operator' => ['name' => auth()->user()?->employee?->name ?? auth()->user()?->name ?? 'System'],
            'approvalReasons' => ['Verified vendor and quantity', 'Urgent stock replenishment', 'Approved negotiated rate'],
            'orders' => $this->orders(),
            'receipts' => $this->receipts(),
            'suppliers' => $this->suppliers(),
        ];
    }

    private function orders(): array
    {
        return PurchaseOrder::with(['supplier', 'lines.ingredient', 'creator', 'approver'])->latest('date')->latest('id')->get()->map(fn ($o) => $this->orderResource($o))->values()->all();
    }

    private function receipts(): array
    {
        return GoodsReceipt::with(['purchaseOrder.supplier', 'lines.ingredient'])->latest('received_date')->latest('id')->get()->map(fn ($g) => $this->receiptResource($g))->values()->all();
    }

    private function suppliers(): array
    {
        return Supplier::with('ingredients')->orderBy('name')->get()->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'contact' => $s->contact_person,
            'phone' => $s->phone,
            'email' => $s->email,
            'gstin' => $s->gstin,
            'address' => $s->address,
            'outstanding' => (float) $s->outstanding,
            'status' => $s->status,
            'items' => $s->ingredients->pluck('name')->values()->all(),
        ])->values()->all();
    }

    private function orderResource(PurchaseOrder $o): array
    {
        return [
            'id' => $o->code,
            'dbId' => $o->id,
            'supplier' => $o->supplier?->name,
            'date' => $o->date?->format('d/m/Y'),
            'expectedDelivery' => $o->expected_delivery?->toDateString(),
            'reference' => $o->reference,
            'notes' => $o->notes,
            'items' => $o->lines->map(fn ($l) => [
                'ingredient' => $l->ingredient?->name,
                'currentStock' => (float) $l->current_stock_snapshot,
                'prevReceived' => $this->receivedQty($o, $l->ingredient_id),
                'qty' => (float) $l->qty,
                'unit' => $l->unit,
                'rate' => (float) $l->rate,
                'tax' => (float) $l->tax_pct,
            ])->values()->all(),
            'discount' => (float) $o->discount,
            'otherCharges' => (float) $o->other_charges,
            'status' => $o->status,
            'createdBy' => $o->creator?->name ?? 'System',
            'approvedBy' => $o->approver?->name,
        ];
    }

    private function receiptResource(GoodsReceipt $g): array
    {
        return [
            'id' => $g->code,
            'dbId' => $g->id,
            'poRef' => $g->purchaseOrder?->code,
            'supplier' => $g->purchaseOrder?->supplier?->name,
            'invoiceNumber' => $g->invoice_number,
            'receivedDate' => $g->received_date?->format('d/m/Y'),
            'items' => $g->lines->map(fn ($l) => [
                'ingredient' => $l->ingredient?->name,
                'ordered' => (float) $l->ordered_qty,
                'prevReceived' => (float) $l->previously_received_qty,
                'receivedNow' => (float) $l->received_now_qty,
                'rejected' => (float) $l->rejected_qty,
            ])->values()->all(),
        ];
    }

    private function validatedOrder(Request $request): array
    {
        return $request->validate([
            'supplier' => ['required', 'string', 'max:255'],
            'expectedDelivery' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'otherCharges' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient' => ['required', 'string', 'exists:ingredients,name'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function nextCode(string $model, string $prefix): string
    {
        $year = now()->format('Y');
        $last = $model::where('code', 'like', "{$prefix}-{$year}-%")
            ->pluck('code')
            ->map(fn ($code) => (int) str_replace("{$prefix}-{$year}-", '', $code))
            ->max() ?? 0;

        return "{$prefix}-{$year}-" . str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    private function receivedQty(PurchaseOrder $order, int $ingredientId): float
    {
        return (float) $order->goodsReceipts()
            ->join('goods_receipt_lines', 'goods_receipts.id', '=', 'goods_receipt_lines.goods_receipt_id')
            ->where('goods_receipt_lines.ingredient_id', $ingredientId)
            ->selectRaw('COALESCE(SUM(received_now_qty - rejected_qty), 0) as qty')
            ->value('qty');
    }

    private function refreshOrderReceiptStatus(PurchaseOrder $order): void
    {
        $order->loadMissing('lines');
        $received = $order->lines->sum(fn ($line) => min((float) $line->qty, $this->receivedQty($order, $line->ingredient_id)));

        if ($received <= 0) {
            $order->update(['status' => 'ordered']);
            return;
        }

        $ordered = $order->lines->sum(fn ($line) => (float) $line->qty);
        $order->update(['status' => $received >= $ordered ? 'received' : 'partially_received']);
    }
}
