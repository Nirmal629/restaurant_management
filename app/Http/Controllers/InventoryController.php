<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\StockCount;
use App\Models\StockLedgerEntry;
use App\Models\Supplier;
use App\Models\Wastage;
use App\Services\RealtimeNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    private const UNITS = ['KG', 'GRAM', 'LITRE', 'ML', 'PCS', 'PACK', 'BOX', 'BOTTLE'];
    private const TX_TYPES = ['OPENING', 'PURCHASE', 'CONSUMPTION', 'WASTAGE', 'ADJUSTMENT', 'RETURN', 'TRANSFER'];
    private const WASTAGE_REASONS = ['Expired', 'Damaged', 'Preparation Waste', 'Overcooked', 'Spillage', 'Other'];

    public function index()
    {
        return view('inventory', ['inventoryModule' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function storeIngredient(Request $request): JsonResponse
    {
        $data = $this->validatedIngredient($request);
        $ingredient = Ingredient::create($this->ingredientAttributes($data) + ['code' => $this->nextIngredientCode()]);

        $this->recordLedger($ingredient, 'OPENING', 'Opening stock', 0, (float) $ingredient->current_stock);
        $this->touchInventory();

        return response()->json(['ingredient' => $this->ingredientResource($ingredient->fresh('supplier')), 'ledger' => $this->ledger(), 'message' => "{$ingredient->name} added"], 201);
    }

    public function updateIngredient(Request $request, Ingredient $ingredient): JsonResponse
    {
        $data = $this->validatedIngredient($request, $ingredient);
        $previous = (float) $ingredient->current_stock;
        $ingredient->update($this->ingredientAttributes($data));
        $change = (float) $ingredient->current_stock - $previous;

        if ($change !== 0.0) {
            $this->recordLedger($ingredient, 'ADJUSTMENT', 'Ingredient master update', $previous, $change);
        }
        $this->touchInventory();

        return response()->json(['ingredient' => $this->ingredientResource($ingredient->fresh('supplier')), 'ledger' => $this->ledger(), 'message' => "{$ingredient->name} updated"]);
    }

    public function adjust(Request $request, Ingredient $ingredient): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(self::TX_TYPES)],
            'qty' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $previous = (float) $ingredient->current_stock;
        $change = (float) $data['qty'];
        $ingredient->update(['current_stock' => max(0, $previous + $change)]);
        $actualChange = (float) $ingredient->current_stock - $previous;
        $this->recordLedger($ingredient, $data['type'], $data['reason'] ?? null, $previous, $actualChange);
        $this->touchInventory();

        return response()->json([
            'ingredient' => $this->ingredientResource($ingredient->fresh('supplier')),
            'ledger' => $this->ledger(),
            'message' => "{$ingredient->name} stock adjusted",
        ]);
    }

    public function destroyIngredient(Ingredient $ingredient): JsonResponse
    {
        $name = $ingredient->name;
        $ingredient->delete();
        $this->touchInventory();

        return response()->json(['ingredients' => $this->ingredients(), 'ledger' => $this->ledger(), 'message' => "{$name} deleted"]);
    }

    public function storeSupplier(Request $request): JsonResponse
    {
        $supplier = Supplier::create($this->supplierAttributes($this->validatedSupplier($request)));
        $this->touchInventory(['inventory']);

        return response()->json([
            'supplier' => $this->supplierResource($supplier),
            'suppliers' => $this->suppliers(),
            'supplierNames' => $this->supplierNames(),
            'message' => "{$supplier->name} added",
        ], 201);
    }

    public function updateSupplier(Request $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($this->supplierAttributes($this->validatedSupplier($request, $supplier)));
        $this->touchInventory(['inventory']);

        return response()->json([
            'supplier' => $this->supplierResource($supplier->fresh('ingredients')),
            'suppliers' => $this->suppliers(),
            'supplierNames' => $this->supplierNames(),
            'ingredients' => $this->ingredients(),
            'message' => "{$supplier->name} updated",
        ]);
    }

    public function destroySupplier(Supplier $supplier): JsonResponse
    {
        if ($supplier->ingredients()->exists()) {
            return response()->json(['message' => 'Remove this supplier from ingredients before deleting.'], 422);
        }

        $name = $supplier->name;
        $supplier->delete();
        $this->touchInventory(['inventory']);

        return response()->json([
            'suppliers' => $this->suppliers(),
            'supplierNames' => $this->supplierNames(),
            'message' => "{$name} deleted",
        ]);
    }

    public function storeWastage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ingredient' => ['required', 'exists:ingredients,name'],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', Rule::in(self::WASTAGE_REASONS)],
            'employee' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $ingredient = Ingredient::where('name', $data['ingredient'])->firstOrFail();
        $previous = (float) $ingredient->current_stock;
        $qty = (float) $data['qty'];
        $ingredient->update(['current_stock' => max(0, $previous - $qty)]);
        $actualChange = (float) $ingredient->current_stock - $previous;

        $wastage = Wastage::create([
            'code' => $this->nextWastageCode(),
            'ingredient_id' => $ingredient->id,
            'qty' => $qty,
            'unit' => $ingredient->unit,
            'reason' => $data['reason'],
            'cost' => round($qty * (float) $ingredient->avg_cost, 2),
            'employee_id' => $this->employeeId($data['employee'] ?? null),
            'date' => now()->toDateString(),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->recordLedger($ingredient, 'WASTAGE', $wastage->code, $previous, $actualChange, $wastage->employee_id);
        $this->touchInventory();

        return response()->json([
            'ingredient' => $this->ingredientResource($ingredient->fresh('supplier')),
            'ledger' => $this->ledger(),
            'wastage' => $this->wastage(),
            'message' => 'Wastage recorded',
        ], 201);
    }

    public function destroyWastage(string $wastage): JsonResponse
    {
        $record = Wastage::where('code', $wastage)->firstOrFail();
        $code = $record->code;
        $record->delete();
        $this->touchInventory(['inventory']);

        return response()->json(['wastage' => $this->wastage(), 'message' => "{$code} deleted"]);
    }

    public function storeCount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.ingredient' => ['required', 'exists:ingredients,name'],
            'lines.*.system' => ['required', 'numeric'],
            'lines.*.physical' => ['required', 'numeric', 'min:0'],
            'lines.*.reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $count = StockCount::create([
                'code' => $this->nextStockCountCode(),
                'date' => now()->toDateString(),
                'status' => 'completed',
                'employee_id' => $this->currentEmployeeId(),
            ]);

            foreach ($data['lines'] as $line) {
                $ingredient = Ingredient::where('name', $line['ingredient'])->firstOrFail();
                $previous = (float) $ingredient->current_stock;
                $physical = (float) $line['physical'];
                $count->lines()->create([
                    'ingredient_id' => $ingredient->id,
                    'system_qty' => $line['system'],
                    'physical_qty' => $physical,
                    'reason' => $line['reason'] ?? null,
                ]);

                if ($previous !== $physical) {
                    $ingredient->update(['current_stock' => $physical]);
                    $this->recordLedger($ingredient, 'ADJUSTMENT', $count->code, $previous, $physical - $previous);
                }
            }
        });
        $this->touchInventory();

        return response()->json([
            'ingredients' => $this->ingredients(),
            'ledger' => $this->ledger(),
            'stockCounts' => $this->stockCounts(),
            'message' => 'Stock count submitted - variances applied',
        ], 201);
    }

    public function destroyCount(string $count): JsonResponse
    {
        $record = StockCount::where('code', $count)->firstOrFail();
        $code = $record->code;
        $record->delete();
        $this->touchInventory(['inventory']);

        return response()->json(['stockCounts' => $this->stockCounts(), 'message' => "{$code} deleted"]);
    }

    public function updateRecipe(Request $request, MenuItem $menuItem): JsonResponse
    {
        $data = $request->validate([
            'lines' => ['present', 'array'],
            'lines.*.ingredient' => ['required', 'exists:ingredients,name'],
            'lines.*.qty' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit' => ['required', Rule::in(self::UNITS)],
        ]);

        $recipe = Recipe::firstOrCreate(['menu_item_id' => $menuItem->id]);
        $recipe->lines()->delete();
        foreach ($data['lines'] as $line) {
            $ingredient = Ingredient::where('name', $line['ingredient'])->firstOrFail();
            $recipe->lines()->create([
                'ingredient_id' => $ingredient->id,
                'qty' => $line['qty'],
                'unit' => $line['unit'],
            ]);
        }
        $this->touchInventory();

        return response()->json(['recipes' => $this->recipes(), 'message' => "{$menuItem->name} recipe saved"]);
    }

    public function export()
    {
        $rows = collect([['Code', 'Name', 'Category', 'Unit', 'Current', 'Minimum', 'Reorder', 'Average Cost', 'Stock Value', 'Supplier', 'Location', 'Status']])
            ->concat(Ingredient::with('supplier')->orderBy('name')->get()->map(fn ($i) => [
                $i->code,
                $i->name,
                $i->category,
                $i->unit,
                (float) $i->current_stock,
                (float) $i->min_stock,
                (float) $i->reorder_level,
                (float) $i->avg_cost,
                $i->stockValue(),
                $i->supplier?->name,
                $i->storage_location,
                $i->stockStatus(),
            ]));

        $csv = $rows->map(fn ($row) => collect($row)->map(fn ($cell) => '"' . str_replace('"', '""', (string) $cell) . '"')->implode(','))->implode("\n");

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory.csv"',
        ]);
    }

    private function payload(): array
    {
        return [
            'venue' => ['name' => config('app.name', 'Restaurant'), 'branch' => 'Ichapur Main Branch'],
            'units' => self::UNITS,
            'categories' => Ingredient::query()->select('category')->distinct()->orderBy('category')->pluck('category')->values()->all(),
            'locations' => Ingredient::query()->whereNotNull('storage_location')->select('storage_location')->distinct()->orderBy('storage_location')->pluck('storage_location')->values()->all(),
            'suppliers' => $this->supplierNames(),
            'supplierRecords' => $this->suppliers(),
            'txTypes' => self::TX_TYPES,
            'wastageReasons' => self::WASTAGE_REASONS,
            'recipes' => $this->recipes(),
            'ingredients' => $this->ingredients(),
            'ledger' => $this->ledger(),
            'wastage' => $this->wastage(),
            'stockCounts' => $this->stockCounts(),
            'employees' => Employee::orderBy('name')->pluck('name')->values()->all(),
            'menuItems' => MenuItem::where('stock_tracked', true)->orderBy('name')->get(['id', 'name'])->values()->all(),
        ];
    }

    private function touchInventory(array $topics = ['inventory', 'menu', 'pos']): void
    {
        app(RealtimeNotifier::class)->touch($topics);
    }

    private function ingredients(): array
    {
        return Ingredient::with('supplier')->latest('id')->get()->map(fn ($i) => $this->ingredientResource($i))->values()->all();
    }

    private function suppliers(): array
    {
        return Supplier::with('ingredients')->orderBy('name')->get()->map(fn ($supplier) => $this->supplierResource($supplier))->values()->all();
    }

    private function supplierNames(): array
    {
        return Supplier::orderBy('name')->pluck('name')->values()->all();
    }

    private function supplierResource(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'contact' => $supplier->contact_person,
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'gstin' => $supplier->gstin,
            'address' => $supplier->address,
            'outstanding' => (float) $supplier->outstanding,
            'status' => $supplier->status,
            'items' => $supplier->ingredients->pluck('name')->values()->all(),
        ];
    }

    private function ingredientResource(Ingredient $ingredient): array
    {
        return [
            'id' => $ingredient->id,
            'code' => $ingredient->code,
            'name' => $ingredient->name,
            'category' => $ingredient->category,
            'unit' => $ingredient->unit,
            'current' => (float) $ingredient->current_stock,
            'min' => (float) $ingredient->min_stock,
            'reorder' => (float) $ingredient->reorder_level,
            'avgCost' => (float) $ingredient->avg_cost,
            'supplier' => $ingredient->supplier?->name,
            'location' => $ingredient->storage_location,
            'status' => $ingredient->stockStatus(),
        ];
    }

    private function ledger(): array
    {
        return StockLedgerEntry::with(['ingredient', 'employee'])->latest('recorded_at')->limit(200)->get()->map(fn ($l) => [
            'at' => $l->recorded_at?->format('d/m/Y H:i'),
            'ingredient' => $l->ingredient?->name,
            'type' => $l->type,
            'ref' => $l->reference ?: '—',
            'prev' => (float) $l->previous_qty,
            'change' => (float) $l->change_qty,
            'next' => (float) $l->new_qty,
            'user' => $l->employee?->name ?? 'System',
        ])->values()->all();
    }

    private function wastage(): array
    {
        return Wastage::with(['ingredient', 'employee'])->latest('date')->latest('id')->get()->map(fn ($w) => [
            'id' => $w->code,
            'ingredient' => $w->ingredient?->name,
            'qty' => (float) $w->qty,
            'unit' => $w->unit,
            'reason' => $w->reason,
            'cost' => (float) $w->cost,
            'employee' => $w->employee?->name ?? 'System',
            'date' => $w->date?->format('d/m/Y'),
            'notes' => $w->notes,
        ])->values()->all();
    }

    private function stockCounts(): array
    {
        return StockCount::with(['employee', 'lines.ingredient'])->latest('date')->latest('id')->get()->map(fn ($s) => [
            'id' => $s->code,
            'date' => $s->date?->format('d/m/Y'),
            'status' => $s->status,
            'by' => $s->employee?->name ?? 'System',
            'lines' => $s->lines->map(fn ($l) => [
                'ingredient' => $l->ingredient?->name,
                'system' => (float) $l->system_qty,
                'physical' => (float) $l->physical_qty,
                'reason' => $l->reason,
            ])->values()->all(),
        ])->values()->all();
    }

    private function recipes(): array
    {
        return Recipe::with(['menuItem', 'lines.ingredient'])->get()
            ->mapWithKeys(fn ($recipe) => [$recipe->menuItem?->name => [
                'sellPrice' => (float) ($recipe->menuItem?->base_price ?? 0),
                'lines' => $recipe->lines->map(fn ($line) => [
                    'ingredient' => $line->ingredient?->name,
                    'qty' => (float) $line->qty,
                    'unit' => $line->unit,
                ])->values()->all(),
            ]])
            ->filter(fn ($value, $key) => filled($key))
            ->all();
    }

    private function validatedIngredient(Request $request, ?Ingredient $ingredient = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(self::UNITS)],
            'current' => ['required', 'numeric', 'min:0'],
            'min' => ['required', 'numeric', 'min:0'],
            'reorder' => ['required', 'numeric', 'min:0'],
            'avgCost' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function validatedSupplier(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($supplier)],
            'contact' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'outstanding' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [], [
            'gstin' => 'GSTIN',
        ]);
    }

    private function supplierAttributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'contact_person' => $data['contact'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'gstin' => $data['gstin'] ?? null,
            'address' => $data['address'] ?? null,
            'outstanding' => $data['outstanding'] ?? 0,
            'status' => $data['status'],
        ];
    }

    private function ingredientAttributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'],
            'current_stock' => $data['current'],
            'min_stock' => $data['min'],
            'reorder_level' => $data['reorder'],
            'avg_cost' => $data['avgCost'],
            'supplier_id' => $this->supplierId($data['supplier'] ?? null),
            'storage_location' => $data['location'] ?? null,
        ];
    }

    private function recordLedger(Ingredient $ingredient, string $type, ?string $reference, float $previous, float $change, ?int $employeeId = null): void
    {
        $ingredient->ledgerEntries()->create([
            'type' => $type,
            'reference' => $reference,
            'previous_qty' => $previous,
            'change_qty' => $change,
            'new_qty' => (float) $ingredient->current_stock,
            'employee_id' => $employeeId ?? $this->currentEmployeeId(),
            'recorded_at' => now(),
        ]);
    }

    private function supplierId(?string $name): ?int
    {
        return $name ? Supplier::firstOrCreate(['name' => $name], ['status' => 'active'])->id : null;
    }

    private function employeeId(?string $name): ?int
    {
        return $name ? Employee::where('name', $name)->value('id') : $this->currentEmployeeId();
    }

    private function currentEmployeeId(): ?int
    {
        return auth()->user()?->employee?->id;
    }

    private function nextIngredientCode(): string
    {
        $last = Ingredient::where('code', 'like', 'ING-%')->get()->map(fn ($i) => (int) str_replace('ING-', '', $i->code))->max() ?? 0;
        return 'ING-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }

    private function nextWastageCode(): string
    {
        $last = Wastage::where('code', 'like', 'WST-%')->get()->map(fn ($w) => (int) str_replace('WST-', '', $w->code))->max() ?? 0;
        return 'WST-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }

    private function nextStockCountCode(): string
    {
        $last = StockCount::where('code', 'like', 'SC-%')->latest('id')->value('id') ?? 0;
        return 'SC-' . now()->format('Y') . '-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }
}
