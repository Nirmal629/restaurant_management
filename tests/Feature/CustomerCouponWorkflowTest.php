<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCouponWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_can_be_created_updated_and_deleted(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Customers', 'Create'],
            ['Customers', 'Edit'],
        ]);

        $create = $this->postJson('/customers/coupons', [
            'code' => 'VIP20',
            'name' => 'VIP Dining',
            'type' => 'percent',
            'value' => 20,
            'minBillAmount' => 1000,
            'maxDiscountAmount' => 400,
            'startsAt' => now()->toDateString(),
            'expiresAt' => now()->addMonth()->toDateString(),
            'usageLimit' => 50,
            'perCustomerLimit' => 1,
            'walkinAllowed' => false,
            'active' => true,
        ])->assertCreated();

        $id = $create->json('coupon.id');

        $this->putJson("/customers/coupons/{$id}", [
            'code' => 'VIP25',
            'name' => 'VIP Dining Updated',
            'type' => 'percent',
            'value' => 25,
            'minBillAmount' => 1200,
            'maxDiscountAmount' => 500,
            'startsAt' => now()->toDateString(),
            'expiresAt' => now()->addMonth()->toDateString(),
            'usageLimit' => 50,
            'perCustomerLimit' => 1,
            'walkinAllowed' => false,
            'active' => true,
        ])->assertOk()
            ->assertJsonPath('coupon.code', 'VIP25');

        $this->deleteJson("/customers/coupons/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('coupons', ['id' => $id]);
    }
}
