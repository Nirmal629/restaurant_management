<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockConsumptionService
{
    public function __construct(private readonly RealtimeNotifier $realtime) {}

    public function assertAvailable(MenuItem $menuItem, int $qty): void
    {
        if ($menuItem->availability !== 'available') {
            throw new RuntimeException("{$menuItem->name} is not available.");
        }

        if (! $menuItem->stock_tracked) {
            return;
        }

        $menuItem->loadMissing('recipe.lines.ingredient');
        $recipe = $menuItem->recipe;

        if (! $recipe || $recipe->lines->isEmpty()) {
            throw new RuntimeException("Recipe is not configured for {$menuItem->name}.");
        }

        foreach ($recipe->lines as $line) {
            $ingredient = $line->ingredient;
            $required = (float) $line->qty * $qty;

            if (! $ingredient || (float) $ingredient->current_stock < $required) {
                $available = $ingredient ? (float) $ingredient->current_stock : 0;
                throw new RuntimeException("Insufficient stock for {$menuItem->name}: {$line->ingredient?->name} needs {$required}, available {$available}.");
            }
        }
    }

    public function consume(OrderItem $item, ?Employee $employee = null): void
    {
        $item->loadMissing('menuItem.recipe.lines.ingredient');

        if ($item->stock_consumed || ! $item->menuItem?->stock_tracked) {
            return;
        }

        $this->assertAvailable($item->menuItem, (int) $item->qty);

        DB::transaction(function () use ($item, $employee) {
            foreach ($item->menuItem->recipe->lines as $line) {
                $ingredient = Ingredient::lockForUpdate()->find($line->ingredient_id);
                $required = (float) $line->qty * (int) $item->qty;
                $previous = (float) $ingredient->current_stock;

                if ($previous < $required) {
                    throw new RuntimeException("Insufficient stock for {$ingredient->name}.");
                }

                $ingredient->update(['current_stock' => $previous - $required]);
                $this->ledger($ingredient, 'SALE_CONSUMPTION', $item, $previous, -$required, $employee);
            }

            $item->update(['stock_consumed' => true]);
        });

        $this->realtime->touch(['inventory', 'menu', 'pos']);
    }

    public function reverse(OrderItem $item, string $type = 'SALE_REVERSAL', ?Employee $employee = null): void
    {
        $item->loadMissing('menuItem.recipe.lines.ingredient');

        if (! $item->stock_consumed || ! $item->menuItem?->stock_tracked || ! $item->menuItem->recipe) {
            return;
        }

        DB::transaction(function () use ($item, $type, $employee) {
            foreach ($item->menuItem->recipe->lines as $line) {
                $ingredient = Ingredient::lockForUpdate()->find($line->ingredient_id);
                $change = (float) $line->qty * (int) $item->qty;
                $previous = (float) $ingredient->current_stock;

                $ingredient->update(['current_stock' => $previous + $change]);
                $this->ledger($ingredient, $type, $item, $previous, $change, $employee);
            }

            $item->update(['stock_consumed' => false]);
        });

        $this->realtime->touch(['inventory', 'menu', 'pos']);
    }

    private function ledger(Ingredient $ingredient, string $type, OrderItem $item, float $previous, float $change, ?Employee $employee): void
    {
        $ingredient->ledgerEntries()->create([
            'type' => $type,
            'reference' => $item->order?->code . ' / item #' . $item->id,
            'previous_qty' => $previous,
            'change_qty' => $change,
            'new_qty' => (float) $ingredient->current_stock,
            'employee_id' => $employee?->id,
            'recorded_at' => now(),
        ]);
    }
}
