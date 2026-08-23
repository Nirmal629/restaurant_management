<?php

namespace Tests\Feature;

use App\Models\KitchenStation;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_item_can_be_updated_from_the_api(): void
    {
        $this->actingAs(User::factory()->create());

        $category = MenuCategory::create(['name' => 'Starters', 'display_order' => 1]);
        $station = KitchenStation::create(['name' => 'Main Kitchen']);
        $modifier = ModifierGroup::create(['name' => 'Spice Level', 'type' => 'single', 'required' => true, 'min_select' => 1, 'max_select' => 1]);

        $item = MenuItem::create([
            'sku' => 'OLD-001',
            'name' => 'Old Item',
            'menu_category_id' => $category->id,
            'diet_type' => 'veg',
            'base_price' => 100,
            'tax_profile' => 'GST 5%',
            'prep_time_minutes' => 10,
            'kitchen_station_id' => $station->id,
            'availability' => 'available',
        ]);

        $response = $this->putJson("/menu/items/{$item->id}", [
            'sku' => 'NEW-001',
            'name' => 'Updated Item',
            'shortName' => 'Updated',
            'category' => $category->id,
            'dietType' => 'nonveg',
            'price' => 250,
            'taxProfile' => 'GST 5%',
            'prepTime' => 18,
            'station' => $station->id,
            'description' => 'Updated description',
            'featured' => true,
            'popular' => true,
            'stockTracked' => true,
            'availability' => 'sold_out',
            'variants' => [['label' => 'Large', 'price' => 350]],
            'modifierGroupIds' => [$modifier->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('item.name', 'Updated Item')
            ->assertJsonPath('item.sku', 'NEW-001')
            ->assertJsonPath('item.variants.0.label', 'Large')
            ->assertJsonPath('item.modifierGroupIds.0', $modifier->id);

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'sku' => 'NEW-001',
            'name' => 'Updated Item',
            'availability' => 'sold_out',
        ]);
    }
}
