<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosInventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_kot_checks_consumes_and_reverses_recipe_stock(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['POS', 'View'],
            ['POS', 'Create'],
            ['POS', 'Cancel'],
        ]);

        [$item, $ingredient] = $this->trackedMenuItem();

        $this->get('/pos')
            ->assertOk()
            ->assertSee('window.posModule', false);

        $create = $this->postJson('/pos/kot', [
            'orderType' => 'takeaway',
            'token' => '501',
            'items' => [
                ['menuItemId' => $item->id, 'qty' => 2, 'modifiers' => [], 'note' => 'Less spicy'],
            ],
        ])->assertCreated()
            ->assertJsonPath('activeOrder.items.0.status', 'sent');

        $this->assertSame(6.0, (float) $ingredient->fresh()->current_stock);
        $this->assertDatabaseHas('stock_ledger_entries', [
            'ingredient_id' => $ingredient->id,
            'type' => 'SALE_CONSUMPTION',
            'change_qty' => -4,
        ]);

        $line = OrderItem::firstOrFail();
        $this->assertTrue((bool) $line->fresh()->stock_consumed);

        $this->postJson('/pos/kot', [
            'orderId' => $create->json('activeOrder.id'),
            'orderType' => 'takeaway',
            'token' => '501',
            'items' => [
                ['menuItemId' => $item->id, 'qty' => 4, 'modifiers' => []],
            ],
        ])->assertUnprocessable();

        $this->patchJson("/pos/items/{$line->id}/cancel", [
            'reason' => 'Guest cancelled',
        ])->assertOk()
            ->assertJsonPath('activeOrder.items.0.status', 'cancelled');

        $this->assertSame(10.0, (float) $ingredient->fresh()->current_stock);
        $this->assertFalse((bool) $line->fresh()->stock_consumed);
        $this->assertDatabaseHas('stock_ledger_entries', [
            'ingredient_id' => $ingredient->id,
            'type' => 'SALE_CANCEL_REVERSAL',
            'change_qty' => 4,
        ]);
    }

    private function trackedMenuItem(): array
    {
        $ingredient = Ingredient::create([
            'code' => 'ING-POS-001',
            'name' => 'Test Paneer',
            'category' => 'Dairy',
            'unit' => 'kg',
            'current_stock' => 10,
            'min_stock' => 1,
            'reorder_level' => 2,
            'avg_cost' => 120,
        ]);
        $category = MenuCategory::create(['name' => 'POS Test', 'display_order' => 1]);
        $item = MenuItem::create([
            'sku' => 'POS-STOCK-001',
            'name' => 'Tracked Paneer Tikka',
            'menu_category_id' => $category->id,
            'diet_type' => 'veg',
            'base_price' => 280,
            'availability' => 'available',
            'stock_tracked' => true,
        ]);
        $recipe = Recipe::create(['menu_item_id' => $item->id]);
        $recipe->lines()->create(['ingredient_id' => $ingredient->id, 'qty' => 2, 'unit' => 'kg']);

        return [$item, $ingredient];
    }
}
