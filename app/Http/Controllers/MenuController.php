<?php

namespace App\Http\Controllers;

use App\Models\Combo;
use App\Models\KitchenStation;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        return view('menu', ['menuModule' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function storeItem(Request $request): JsonResponse
    {
        $data = $this->validatedItem($request);
        $item = MenuItem::create($this->itemAttributes($data));
        $this->syncItemChildren($item, $data);

        return response()->json(['item' => $this->itemResource($item->fresh($this->itemRelations())), 'message' => "{$item->name} added"], 201);
    }

    public function updateItem(Request $request, MenuItem $item): JsonResponse
    {
        $data = $this->validatedItem($request, $item);
        $item->update($this->itemAttributes($data));
        $this->syncItemChildren($item, $data);

        return response()->json(['item' => $this->itemResource($item->fresh($this->itemRelations())), 'message' => "{$item->name} updated"]);
    }

    public function availability(Request $request, MenuItem $item): JsonResponse
    {
        $data = $request->validate(['availability' => ['required', Rule::in(['available', 'sold_out', 'temp_unavailable'])]]);
        $item->update(['availability' => $data['availability']]);

        return response()->json(['item' => $this->itemResource($item->fresh($this->itemRelations())), 'message' => "{$item->name} availability updated"]);
    }

    public function duplicate(MenuItem $item): JsonResponse
    {
        $copy = $item->replicate(['sku']);
        $copy->sku = $this->copySku($item->sku);
        $copy->name = "{$item->name} (Copy)";
        $copy->save();
        $copy->modifierGroups()->sync($item->modifierGroups->pluck('id'));
        foreach ($item->variants as $variant) {
            $copy->variants()->create(['label' => $variant->label, 'price' => $variant->price]);
        }

        return response()->json(['item' => $this->itemResource($copy->fresh($this->itemRelations())), 'message' => "{$item->name} duplicated"], 201);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $category = MenuCategory::create($this->validatedCategory($request) + ['display_order' => MenuCategory::max('display_order') + 1]);

        return response()->json(['category' => $this->categoryResource($category), 'message' => 'Category saved'], 201);
    }

    public function updateCategory(Request $request, MenuCategory $category): JsonResponse
    {
        $category->update($this->validatedCategory($request));

        return response()->json(['category' => $this->categoryResource($category), 'message' => 'Category saved']);
    }

    public function storeModifier(Request $request): JsonResponse
    {
        $data = $this->validatedModifier($request);
        $group = ModifierGroup::create($this->modifierAttributes($data));
        $this->syncModifierOptions($group, $data['options']);

        return response()->json(['modifierGroup' => $this->modifierResource($group->fresh('options')), 'message' => 'Modifier group saved'], 201);
    }

    public function updateModifier(Request $request, ModifierGroup $group): JsonResponse
    {
        $data = $this->validatedModifier($request);
        $group->update($this->modifierAttributes($data));
        $this->syncModifierOptions($group, $data['options']);

        return response()->json(['modifierGroup' => $this->modifierResource($group->fresh('options')), 'message' => 'Modifier group saved']);
    }

    public function storeCombo(Request $request): JsonResponse
    {
        $data = $this->validatedCombo($request);
        $combo = Combo::create(['name' => $data['name'], 'price' => $data['price']]);
        $this->syncComboItems($combo, $data['items']);

        return response()->json(['combo' => $this->comboResource($combo->fresh('items.menuItem')), 'message' => 'Combo saved'], 201);
    }

    public function updateCombo(Request $request, Combo $combo): JsonResponse
    {
        $data = $this->validatedCombo($request);
        $combo->update(['name' => $data['name'], 'price' => $data['price']]);
        $this->syncComboItems($combo, $data['items']);

        return response()->json(['combo' => $this->comboResource($combo->fresh('items.menuItem')), 'message' => 'Combo saved']);
    }

    private function payload(): array
    {
        return [
            'venue' => ['name' => config('app.name', 'Restaurant'), 'branch' => 'Ichapur Main Branch'],
            'categories' => MenuCategory::orderBy('display_order')->orderBy('name')->get()->map(fn ($c) => $this->categoryResource($c))->values()->all(),
            'stations' => KitchenStation::orderBy('name')->get()->map(fn ($s) => $this->stationResource($s))->values()->all(),
            'taxProfiles' => ['GST 5%', 'GST 12%', 'GST 18%', 'Exempt'],
            'items' => MenuItem::with($this->itemRelations())->latest('id')->get()->map(fn ($i) => $this->itemResource($i))->values()->all(),
            'modifierGroups' => ModifierGroup::with('options')->latest('id')->get()->map(fn ($g) => $this->modifierResource($g))->values()->all(),
            'combos' => Combo::with('items.menuItem')->latest('id')->get()->map(fn ($c) => $this->comboResource($c))->values()->all(),
        ];
    }

    private function itemRelations(): array
    {
        return ['category', 'station', 'variants', 'modifierGroups'];
    }

    private function itemResource(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->name,
            'shortName' => $item->short_name,
            'category' => $item->menu_category_id,
            'dietType' => $item->diet_type,
            'price' => (float) $item->base_price,
            'taxProfile' => $item->tax_profile,
            'prepTime' => $item->prep_time_minutes,
            'station' => $item->kitchen_station_id,
            'description' => $item->description,
            'featured' => $item->featured,
            'popular' => $item->popular,
            'stockTracked' => $item->stock_tracked,
            'availability' => $item->availability,
            'variants' => $item->variants->map(fn ($v) => ['label' => $v->label, 'price' => (float) $v->price])->values()->all(),
            'modifierGroupIds' => $item->modifierGroups->pluck('id')->values()->all(),
        ];
    }

    private function categoryResource(MenuCategory $category): array
    {
        return ['key' => $category->id, 'label' => $category->name];
    }

    private function stationResource(KitchenStation $station): array
    {
        return ['key' => $station->id, 'label' => $station->name];
    }

    private function modifierResource(ModifierGroup $group): array
    {
        return [
            'id' => $group->id,
            'label' => $group->name,
            'type' => $group->type,
            'required' => $group->required,
            'min' => $group->min_select,
            'max' => $group->max_select,
            'options' => $group->options->map(fn ($o) => ['label' => $o->label, 'delta' => (float) $o->price_delta])->values()->all(),
        ];
    }

    private function comboResource(Combo $combo): array
    {
        return [
            'id' => $combo->id,
            'name' => $combo->name,
            'price' => (float) $combo->price,
            'items' => $combo->items->map(fn ($i) => ['name' => $i->menuItem?->name, 'qty' => $i->qty])->filter(fn ($i) => filled($i['name']))->values()->all(),
        ];
    }

    private function validatedItem(Request $request, ?MenuItem $item = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('menu_items', 'sku')->ignore($item)],
            'name' => ['required', 'string', 'max:255'],
            'shortName' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'exists:menu_categories,id'],
            'dietType' => ['required', Rule::in(['veg', 'nonveg', 'egg'])],
            'price' => ['required', 'numeric', 'min:0'],
            'taxProfile' => ['required', 'string', 'max:50'],
            'prepTime' => ['required', 'integer', 'min:0'],
            'station' => ['nullable', 'exists:kitchen_stations,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'featured' => ['boolean'],
            'popular' => ['boolean'],
            'stockTracked' => ['boolean'],
            'availability' => ['required', Rule::in(['available', 'sold_out', 'temp_unavailable'])],
            'variants' => ['present', 'array'],
            'variants.*.label' => ['required_with:variants.*.price', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants.*.label', 'numeric', 'min:0'],
            'modifierGroupIds' => ['present', 'array'],
            'modifierGroupIds.*' => ['integer', 'exists:modifier_groups,id'],
        ]);
    }

    private function itemAttributes(array $data): array
    {
        return [
            'sku' => strtoupper($data['sku']),
            'name' => $data['name'],
            'short_name' => $data['shortName'] ?? null,
            'menu_category_id' => $data['category'],
            'diet_type' => $data['dietType'],
            'base_price' => $data['price'],
            'tax_profile' => $data['taxProfile'],
            'prep_time_minutes' => $data['prepTime'],
            'kitchen_station_id' => $data['station'] ?? null,
            'description' => $data['description'] ?? null,
            'featured' => $data['featured'] ?? false,
            'popular' => $data['popular'] ?? false,
            'stock_tracked' => $data['stockTracked'] ?? false,
            'availability' => $data['availability'],
        ];
    }

    private function syncItemChildren(MenuItem $item, array $data): void
    {
        $item->variants()->delete();
        foreach ($data['variants'] ?? [] as $variant) {
            if (filled($variant['label'] ?? null)) {
                $item->variants()->create(['label' => $variant['label'], 'price' => $variant['price']]);
            }
        }
        $item->modifierGroups()->sync($data['modifierGroupIds'] ?? []);
    }

    private function validatedCategory(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255']]);
    }

    private function validatedModifier(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['single', 'multi'])],
            'required' => ['boolean'],
            'min' => ['required', 'integer', 'min:0'],
            'max' => ['required', 'integer', 'min:1'],
            'options' => ['required', 'array', 'min:1'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.delta' => ['nullable', 'numeric'],
        ]);
    }

    private function modifierAttributes(array $data): array
    {
        return ['name' => $data['label'], 'type' => $data['type'], 'required' => $data['required'] ?? false, 'min_select' => $data['min'], 'max_select' => $data['max']];
    }

    private function syncModifierOptions(ModifierGroup $group, array $options): void
    {
        $group->options()->delete();
        foreach ($options as $option) {
            $group->options()->create(['label' => $option['label'], 'price_delta' => $option['delta'] ?? 0]);
        }
    }

    private function validatedCombo(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'exists:menu_items,name'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function syncComboItems(Combo $combo, array $items): void
    {
        $combo->items()->delete();
        foreach ($items as $line) {
            $menuItem = MenuItem::where('name', $line['name'])->first();
            if ($menuItem) {
                $combo->items()->create(['menu_item_id' => $menuItem->id, 'qty' => $line['qty']]);
            }
        }
    }

    private function copySku(string $sku): string
    {
        $candidate = Str::limit($sku, 235, '') . '-COPY';
        $suffix = 2;
        while (MenuItem::where('sku', $candidate)->exists()) {
            $candidate = Str::limit($sku, 230, '') . "-COPY{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
