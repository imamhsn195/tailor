<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Membership;
use App\Models\Order;
use App\Models\CustomerComment;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /**
     * Test that a customer can be created.
     */
    public function test_customer_can_be_created(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'mobile' => '1234567890',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        $this->assertEquals('Test Customer', $customer->name);
    }

    /**
     * Test that customer discount_percentage is cast to decimal.
     */
    public function test_customer_discount_percentage_is_decimal(): void
    {
        $customer = Customer::factory()->create(['discount_percentage' => 15.50]);

        $this->assertIsFloat($customer->discount_percentage);
        $this->assertEquals(15.50, $customer->discount_percentage);
    }

    /**
     * Test that customer is_active is cast to boolean.
     */
    public function test_customer_is_active_is_boolean(): void
    {
        $customer = Customer::factory()->create(['is_active' => true]);

        $this->assertIsBool($customer->is_active);
        $this->assertTrue($customer->is_active);
    }

    /**
     * Test that customer can have memberships.
     */
    public function test_customer_can_have_memberships(): void
    {
        $customer = Customer::factory()->create();
        $membership = Membership::factory()->create();

        $customer->memberships()->attach($membership->id, [
            'joined_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $this->assertTrue($customer->memberships->contains($membership));
        $this->assertEquals(1, $customer->memberships->count());
    }

    /**
     * Test that customer can have orders.
     */
    public function test_customer_can_have_orders(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $this->assertTrue($customer->orders->contains($order));
        $this->assertEquals(1, $customer->orders->count());
    }

    /**
     * Test that customer can have comments.
     */
    public function test_customer_can_have_comments(): void
    {
        $customer = Customer::factory()->create();
        $comment = CustomerComment::factory()->create(['customer_id' => $customer->id]);

        $this->assertTrue($customer->comments->contains($comment));
        $this->assertEquals(1, $customer->comments->count());
    }

    /**
     * Test that customer uses soft deletes.
     */
    public function test_customer_uses_soft_deletes(): void
    {
        $customer = Customer::factory()->create();
        $customerId = $customer->id;

        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customerId]);
        $this->assertNull(Customer::find($customerId));
        $this->assertNotNull(Customer::withTrashed()->find($customerId));
    }
}
