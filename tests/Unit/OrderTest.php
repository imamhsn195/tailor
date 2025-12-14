<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\OrderItem;
use App\Models\OrderFabric;
use App\Models\Measurement;
use App\Models\Cutting;
use App\Models\Delivery;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /**
     * Test that an order can be created.
     */
    public function test_order_can_be_created(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-001',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-001',
            'status' => 'pending',
        ]);

        $this->assertEquals('ORD-001', $order->order_number);
    }

    /**
     * Test that order belongs to a customer.
     */
    public function test_order_belongs_to_customer(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $order->customer);
        $this->assertEquals($customer->id, $order->customer->id);
    }

    /**
     * Test that order belongs to a branch.
     */
    public function test_order_belongs_to_branch(): void
    {
        $branch = Branch::factory()->create();
        $order = Order::factory()->create(['branch_id' => $branch->id]);

        $this->assertInstanceOf(Branch::class, $order->branch);
        $this->assertEquals($branch->id, $order->branch->id);
    }

    /**
     * Test that order can have items.
     */
    public function test_order_can_have_items(): void
    {
        $order = Order::factory()->create();
        $product = \App\Models\Product::factory()->create();
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        $this->assertTrue($order->items->contains($item));
        $this->assertEquals(1, $order->items->count());
    }

    /**
     * Test that order can have fabrics.
     */
    public function test_order_can_have_fabrics(): void
    {
        $order = Order::factory()->create();
        $fabric = OrderFabric::create([
            'order_id' => $order->id,
            'fabric_name' => 'Test Fabric',
            'quantity' => 2.5,
            'price' => 50.00,
        ]);

        $this->assertTrue($order->fabrics->contains($fabric));
        $this->assertEquals(1, $order->fabrics->count());
    }

    /**
     * Test that order can have measurements.
     */
    public function test_order_can_have_measurements(): void
    {
        $order = Order::factory()->create();
        $product = \App\Models\Product::factory()->create();
        $measurement = Measurement::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'measurement_data' => ['chest' => 40],
        ]);

        $this->assertTrue($order->measurements->contains($measurement));
        $this->assertEquals(1, $order->measurements->count());
    }

    /**
     * Test that order can have cuttings.
     */
    public function test_order_can_have_cuttings(): void
    {
        $order = Order::factory()->create();
        $product = \App\Models\Product::factory()->create();
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);
        $cuttingMaster = \App\Models\CuttingMaster::factory()->create();
        $cutting = Cutting::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'cutting_master_id' => $cuttingMaster->id,
            'cutting_date' => now(),
            'quantity' => 1,
        ]);

        $this->assertTrue($order->cuttings->contains($cutting));
        $this->assertEquals(1, $order->cuttings->count());
    }

    /**
     * Test that order can have deliveries.
     */
    public function test_order_can_have_deliveries(): void
    {
        $order = Order::factory()->create();
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'delivery_date' => now(),
            'delivered_amount' => 100.00,
        ]);

        $this->assertTrue($order->deliveries->contains($delivery));
        $this->assertEquals(1, $order->deliveries->count());
    }

    /**
     * Test that order amounts are cast to decimal.
     */
    public function test_order_amounts_are_decimal(): void
    {
        $order = Order::factory()->create([
            'design_charge' => 100.50,
            'embroidery_charge' => 50.25,
            'fabrics_amount' => 200.75,
            'tailor_amount' => 300.00,
            'total_amount' => 651.50,
            'discount_amount' => 50.00,
            'net_payable' => 601.50,
            'paid_amount' => 300.00,
            'due_amount' => 301.50,
        ]);

        // SQLite returns decimals as strings, so check numeric value
        $this->assertIsNumeric($order->design_charge);
        $this->assertIsNumeric($order->total_amount);
        $this->assertIsNumeric($order->net_payable);
        $this->assertEquals(100.50, (float) $order->design_charge);
        $this->assertEquals(651.50, (float) $order->total_amount);
    }

    /**
     * Test that order dates are cast to date.
     */
    public function test_order_dates_are_date(): void
    {
        $orderDate = now()->toDateString();
        $deliveryDate = now()->addDays(7)->toDateString();

        $order = Order::factory()->create([
            'order_date' => $orderDate,
            'delivery_date' => $deliveryDate,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $order->order_date);
        $this->assertEquals($orderDate, $order->order_date->toDateString());
    }

    /**
     * Test that order uses soft deletes.
     */
    public function test_order_uses_soft_deletes(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        $order->delete();

        $this->assertSoftDeleted('orders', ['id' => $orderId]);
        $this->assertNull(Order::find($orderId));
        $this->assertNotNull(Order::withTrashed()->find($orderId));
    }
}
