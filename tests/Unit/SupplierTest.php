<?php

namespace Tests\Unit;

use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\Branch;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    /**
     * Test that a supplier can be created.
     */
    public function test_supplier_can_be_created(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
        ]);

        $this->assertEquals('Test Supplier', $supplier->name);
    }

    /**
     * Test that supplier amounts are cast to decimal.
     */
    public function test_supplier_amounts_are_decimal(): void
    {
        $supplier = Supplier::factory()->create([
            'discount_percentage' => 10.50,
            'total_purchase_amount' => 50000.75,
            'total_paid_amount' => 30000.25,
            'total_due_amount' => 20000.50,
        ]);

        $this->assertIsFloat($supplier->discount_percentage);
        $this->assertIsFloat($supplier->total_purchase_amount);
        $this->assertIsFloat($supplier->total_due_amount);
        $this->assertEquals(10.50, $supplier->discount_percentage);
        $this->assertEquals(50000.75, $supplier->total_purchase_amount);
    }

    /**
     * Test that supplier is_active is cast to boolean.
     */
    public function test_supplier_is_active_is_boolean(): void
    {
        $supplier = Supplier::factory()->create(['is_active' => true]);

        $this->assertIsBool($supplier->is_active);
        $this->assertTrue($supplier->is_active);
    }

    /**
     * Test that supplier can have purchases.
     */
    public function test_supplier_can_have_purchases(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_number' => 'PUR-001',
            'purchase_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 500.00,
            'due_amount' => 500.00,
        ]);

        $this->assertTrue($supplier->purchases->contains($purchase));
        $this->assertEquals(1, $supplier->purchases->count());
    }

    /**
     * Test that supplier can have payments.
     */
    public function test_supplier_can_have_payments(): void
    {
        $supplier = Supplier::factory()->create();
        $payment = SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'payment_date' => now(),
            'amount' => 500.00,
            'payment_method' => 'cash',
        ]);

        $this->assertTrue($supplier->payments->contains($payment));
        $this->assertEquals(1, $supplier->payments->count());
    }

    /**
     * Test that supplier getAccountBalance returns total_due_amount.
     */
    public function test_supplier_get_account_balance(): void
    {
        $supplier = Supplier::factory()->create([
            'total_due_amount' => 15000.50,
        ]);

        $this->assertEquals(15000.50, $supplier->getAccountBalance());
    }

    /**
     * Test that supplier uses soft deletes.
     */
    public function test_supplier_uses_soft_deletes(): void
    {
        $supplier = Supplier::factory()->create();
        $supplierId = $supplier->id;

        $supplier->delete();

        $this->assertSoftDeleted('suppliers', ['id' => $supplierId]);
        $this->assertNull(Supplier::find($supplierId));
        $this->assertNotNull(Supplier::withTrashed()->find($supplierId));
    }
}
