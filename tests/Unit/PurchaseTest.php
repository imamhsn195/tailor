<?php

namespace Tests\Unit;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\User;
use App\Models\PurchaseItem;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    /**
     * Test that a purchase can be created.
     */
    public function test_purchase_can_be_created(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        
        $purchase = Purchase::create([
            'purchase_number' => 'PUR-001',
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_date' => now(),
            'total_amount' => 1000.00,
            'paid_amount' => 500.00,
            'due_amount' => 500.00,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('purchases', [
            'purchase_number' => 'PUR-001',
            'status' => 'pending',
        ]);

        $this->assertEquals('PUR-001', $purchase->purchase_number);
    }

    /**
     * Test that purchase belongs to a supplier.
     */
    public function test_purchase_belongs_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_number' => 'PUR-001',
            'purchase_date' => now(),
            'total_amount' => 1000.00,
        ]);

        $this->assertInstanceOf(Supplier::class, $purchase->supplier);
        $this->assertEquals($supplier->id, $purchase->supplier->id);
    }

    /**
     * Test that purchase belongs to a branch.
     */
    public function test_purchase_belongs_to_branch(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_number' => 'PUR-001',
            'purchase_date' => now(),
            'total_amount' => 1000.00,
        ]);

        $this->assertInstanceOf(Branch::class, $purchase->branch);
        $this->assertEquals($branch->id, $purchase->branch->id);
    }

    /**
     * Test that purchase can have items.
     */
    public function test_purchase_can_have_items(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_number' => 'PUR-001',
            'purchase_date' => now(),
            'total_amount' => 1000.00,
        ]);

        $item = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_name' => 'Test Product',
            'quantity' => 10,
            'unit_price' => 100.00,
            'total_price' => 1000.00,
        ]);

        $this->assertTrue($purchase->items->contains($item));
        $this->assertEquals(1, $purchase->items->count());
    }

    /**
     * Test that purchase amounts are cast to decimal.
     */
    public function test_purchase_amounts_are_decimal(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_number' => 'PUR-001',
            'purchase_date' => now(),
            'subtotal' => 1000.50,
            'discount_amount' => 50.25,
            'vat_amount' => 150.75,
            'total_amount' => 1101.00,
            'paid_amount' => 500.00,
            'due_amount' => 601.00,
        ]);

        // SQLite returns decimals as strings, so check numeric value
        $this->assertIsNumeric($purchase->subtotal);
        $this->assertIsNumeric($purchase->total_amount);
        $this->assertIsNumeric($purchase->due_amount);
        $this->assertEquals(1000.50, (float) $purchase->subtotal);
        $this->assertEquals(1101.00, (float) $purchase->total_amount);
    }

    /**
     * Test that purchase date is cast to date.
     */
    public function test_purchase_date_is_date(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        $purchaseDate = now()->toDateString();
        
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_number' => 'PUR-001',
            'purchase_date' => $purchaseDate,
            'total_amount' => 1000.00,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $purchase->purchase_date);
        $this->assertEquals($purchaseDate, $purchase->purchase_date->toDateString());
    }

    /**
     * Test that purchase uses soft deletes.
     */
    public function test_purchase_uses_soft_deletes(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'purchase_number' => 'PUR-001',
            'purchase_date' => now(),
            'total_amount' => 1000.00,
        ]);
        
        $purchaseId = $purchase->id;

        $purchase->delete();

        $this->assertSoftDeleted('purchases', ['id' => $purchaseId]);
        $this->assertNull(Purchase::find($purchaseId));
        $this->assertNotNull(Purchase::withTrashed()->find($purchaseId));
    }
}
