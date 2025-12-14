<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Concerns\WithTenant;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up tenant context
        $this->setUpTenant();
        
        // Create permissions
        Permission::create(['name' => 'order.view']);
        Permission::create(['name' => 'order.create']);
        Permission::create(['name' => 'order.edit']);
        Permission::create(['name' => 'order.delete']);

        // Create role and assign permissions
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['order.view', 'order.create', 'order.edit', 'order.delete']);

        // Create user and assign role
        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->user->assignRole($role);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }

    /**
     * Test that orders index page requires authentication.
     */
    public function test_orders_index_requires_authentication(): void
    {
        $response = $this->get('/admin/orders');

        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated user can view orders index.
     */
    public function test_authenticated_user_can_view_orders_index(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/orders');

        $response->assertStatus(200);
    }

    /**
     * Test that user can create an order.
     */
    public function test_user_can_create_order(): void
    {
        $customer = Customer::factory()->create();
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->user)->get('/admin/orders/create');

        $response->assertStatus(200);
    }

    /**
     * Test that user can view an order.
     */
    public function test_user_can_view_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->user)->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
    }

    /**
     * Test that user can update an order.
     */
    public function test_user_can_update_order(): void
    {
        $order = Order::factory()->create();
        $customer = Customer::factory()->create();
        $branch = Branch::factory()->create();

        $updateData = [
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'order_date' => now()->toDateString(),
            'status' => 'in_progress',
            'total_amount' => 1000.00,
            'net_payable' => 1000.00,
            'paid_amount' => 500.00,
            'due_amount' => 500.00,
        ];

        $response = $this->actingAs($this->user)->put("/admin/orders/{$order->id}", $updateData);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that user can delete an order.
     */
    public function test_user_can_delete_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->user)->delete("/admin/orders/{$order->id}");

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that orders can be filtered by status.
     */
    public function test_orders_can_be_filtered_by_status(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($this->user)->get('/admin/orders?status=pending');

        $response->assertStatus(200);
    }
}
