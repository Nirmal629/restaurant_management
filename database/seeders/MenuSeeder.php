<?php

namespace Database\Seeders;

use App\Models\Combo;
use App\Models\KitchenStation;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /** Mirrors resources/js/menu/demo-data.js exactly. */
    public function run(): void
    {
        $categories = [];
        foreach (['Starters', 'Biryani', 'Indian', 'Chinese', 'Tandoor', 'Rice', 'Bread', 'Desserts', 'Beverages', 'Combos'] as $i => $name) {
            $categories[$name] = MenuCategory::firstOrCreate(['name' => $name], ['display_order' => $i]);
        }

        $stations = [];
        foreach (['Main Kitchen', 'Tandoor', 'Chinese', 'Beverage', 'Dessert', 'Bar'] as $name) {
            $stations[$name] = KitchenStation::firstOrCreate(['name' => $name]);
        }

        $items = [
            ['sku' => 'BRY-001', 'name' => 'Chicken Biryani', 'cat' => 'Biryani', 'diet' => 'nonveg', 'price' => 320, 'prep' => 18, 'station' => 'Main Kitchen', 'popular' => true, 'stock' => true,
                'variants' => [['Regular', 320], ['Large', 420], ['Family', 720]], 'mods' => ['Spice Level', 'Add-Ons']],
            ['sku' => 'BRY-002', 'name' => 'Mutton Biryani', 'cat' => 'Biryani', 'diet' => 'nonveg', 'price' => 420, 'prep' => 22, 'station' => 'Main Kitchen', 'stock' => true, 'mods' => ['Spice Level']],
            ['sku' => 'STR-001', 'name' => 'Paneer Tikka', 'cat' => 'Starters', 'diet' => 'veg', 'price' => 280, 'prep' => 15, 'station' => 'Tandoor', 'popular' => true, 'mods' => ['Spice Level']],
            ['sku' => 'STR-002', 'name' => 'Chicken Tikka', 'cat' => 'Starters', 'diet' => 'nonveg', 'price' => 360, 'prep' => 16, 'station' => 'Tandoor', 'mods' => ['Spice Level']],
            ['sku' => 'CHN-001', 'name' => 'Veg Fried Rice', 'cat' => 'Chinese', 'diet' => 'veg', 'price' => 220, 'prep' => 12, 'station' => 'Chinese'],
            ['sku' => 'CHN-002', 'name' => 'Chicken Fried Rice', 'cat' => 'Chinese', 'diet' => 'nonveg', 'price' => 280, 'prep' => 12, 'station' => 'Chinese', 'popular' => true],
            ['sku' => 'CHN-003', 'name' => 'Chilli Chicken', 'cat' => 'Chinese', 'diet' => 'nonveg', 'price' => 340, 'prep' => 14, 'station' => 'Chinese', 'availability' => 'sold_out'],
            ['sku' => 'BRD-001', 'name' => 'Butter Naan', 'cat' => 'Bread', 'diet' => 'veg', 'price' => 50, 'prep' => 8, 'station' => 'Tandoor', 'popular' => true],
            ['sku' => 'BEV-001', 'name' => 'Coke', 'cat' => 'Beverages', 'diet' => 'veg', 'price' => 60, 'prep' => 2, 'station' => 'Beverage'],
            ['sku' => 'BEV-002', 'name' => 'Tea', 'cat' => 'Beverages', 'diet' => 'veg', 'price' => 30, 'prep' => 4, 'station' => 'Beverage'],
            ['sku' => 'BEV-003', 'name' => 'Coffee', 'cat' => 'Beverages', 'diet' => 'veg', 'price' => 50, 'prep' => 4, 'station' => 'Beverage'],
            ['sku' => 'DES-001', 'name' => 'Ice Cream', 'cat' => 'Desserts', 'diet' => 'veg', 'price' => 120, 'prep' => 3, 'station' => 'Dessert', 'availability' => 'temp_unavailable'],
            ['sku' => 'IND-001', 'name' => 'Dal Makhani', 'cat' => 'Indian', 'diet' => 'veg', 'price' => 240, 'prep' => 14, 'station' => 'Main Kitchen'],
            ['sku' => 'IND-002', 'name' => 'Butter Chicken', 'cat' => 'Indian', 'diet' => 'nonveg', 'price' => 380, 'prep' => 18, 'station' => 'Main Kitchen', 'featured' => true, 'mods' => ['Spice Level']],
            ['sku' => 'RIC-001', 'name' => 'Steamed Rice', 'cat' => 'Rice', 'diet' => 'veg', 'price' => 120, 'prep' => 8, 'station' => 'Main Kitchen'],
            ['sku' => 'CMB-001', 'name' => 'Chicken Burger', 'cat' => 'Combos', 'diet' => 'nonveg', 'price' => 220, 'prep' => 12, 'station' => 'Main Kitchen', 'mods' => ['Size']],
        ];

        $modifierGroups = [
            'Spice Level' => ModifierGroup::firstOrCreate(['name' => 'Spice Level'], ['type' => 'single', 'required' => true, 'min_select' => 1, 'max_select' => 1]),
            'Add-Ons' => ModifierGroup::firstOrCreate(['name' => 'Add-Ons'], ['type' => 'multi', 'required' => false, 'min_select' => 0, 'max_select' => 4]),
            'Size' => ModifierGroup::firstOrCreate(['name' => 'Size'], ['type' => 'single', 'required' => true, 'min_select' => 1, 'max_select' => 1]),
        ];
        $optionSeed = [
            'Spice Level' => [['Mild', 0], ['Medium', 0], ['Spicy', 0]],
            'Add-Ons' => [['Extra Chicken', 100], ['Extra Egg', 30], ['Extra Gravy', 40]],
            'Size' => [['Regular', 0], ['Large', 60]],
        ];
        foreach ($optionSeed as $groupName => $options) {
            foreach ($options as [$label, $delta]) {
                $modifierGroups[$groupName]->options()->firstOrCreate(['label' => $label], ['price_delta' => $delta]);
            }
        }

        $menuItemsByName = [];
        foreach ($items as $data) {
            $item = MenuItem::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'menu_category_id' => $categories[$data['cat']]->id,
                    'diet_type' => $data['diet'],
                    'base_price' => $data['price'],
                    'prep_time_minutes' => $data['prep'],
                    'kitchen_station_id' => $stations[$data['station']]->id,
                    'availability' => $data['availability'] ?? 'available',
                    'featured' => $data['featured'] ?? false,
                    'popular' => $data['popular'] ?? false,
                    'stock_tracked' => $data['stock'] ?? false,
                ]
            );
            $menuItemsByName[$data['name']] = $item;

            foreach ($data['variants'] ?? [] as [$label, $price]) {
                $item->variants()->firstOrCreate(['label' => $label], ['price' => $price]);
            }

            if (! empty($data['mods'])) {
                $item->modifierGroups()->syncWithoutDetaching(collect($data['mods'])->map(fn ($m) => $modifierGroups[$m]->id));
            }
        }

        $combos = [
            ['name' => 'Biryani Combo', 'price' => 399, 'items' => [['Chicken Biryani', 1], ['Coke', 1]]],
            ['name' => 'Family Feast', 'price' => 1299, 'items' => [['Mutton Biryani', 2], ['Butter Naan', 4], ['Coke', 2]]],
        ];
        foreach ($combos as $data) {
            $combo = Combo::firstOrCreate(['name' => $data['name']], ['price' => $data['price']]);
            foreach ($data['items'] as [$itemName, $qty]) {
                if (isset($menuItemsByName[$itemName])) {
                    $combo->items()->firstOrCreate(['menu_item_id' => $menuItemsByName[$itemName]->id], ['qty' => $qty]);
                }
            }
        }
    }
}
