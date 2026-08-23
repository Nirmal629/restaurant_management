<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySupplierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_can_be_created_updated_and_deleted_from_inventory(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Inventory', 'Create'],
            ['Inventory', 'Edit'],
        ]);

        $create = $this->postJson('/inventory/suppliers', [
            'name' => 'North Market Foods',
            'contact' => 'Debashish Pal',
            'phone' => '9830011122',
            'email' => 'orders@example.test',
            'gstin' => '19AABCB1234K1Z1',
            'address' => 'Ichapur Industrial Area',
            'outstanding' => 2500,
            'status' => 'active',
        ]);

        $create->assertCreated()
            ->assertJsonPath('supplier.name', 'North Market Foods')
            ->assertJsonPath('supplier.contact', 'Debashish Pal');

        $supplier = Supplier::where('name', 'North Market Foods')->firstOrFail();

        $update = $this->putJson("/inventory/suppliers/{$supplier->id}", [
            'name' => 'North Market Foods Updated',
            'contact' => 'Anita Das',
            'phone' => '9830011123',
            'email' => 'purchase@example.test',
            'gstin' => '19AABCB1234K1Z2',
            'address' => 'North 24 Parganas',
            'outstanding' => 1200,
            'status' => 'inactive',
        ]);

        $update->assertOk()
            ->assertJsonPath('supplier.name', 'North Market Foods Updated')
            ->assertJsonPath('supplier.status', 'inactive');

        $this->deleteJson("/inventory/suppliers/{$supplier->id}")
            ->assertOk()
            ->assertJsonPath('supplierNames', []);

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
