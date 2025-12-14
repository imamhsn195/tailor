<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Concerns\WithTenant;

class CustomerTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up tenant context
        $this->setUpTenant();
        
        // Create permissions
        Permission::create(['name' => 'customer.view']);
        Permission::create(['name' => 'customer.create']);
        Permission::create(['name' => 'customer.edit']);
        Permission::create(['name' => 'customer.delete']);

        // Create role and assign permissions
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['customer.view', 'customer.create', 'customer.edit', 'customer.delete']);

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
     * Test that customers index page requires authentication.
     */
    public function test_customers_index_requires_authentication(): void
    {
        $response = $this->get('/admin/customers');

        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated user can view customers index.
     */
    public function test_authenticated_user_can_view_customers_index(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/customers');

        $response->assertStatus(200);
    }

    /**
     * Test that user can create a customer.
     */
    public function test_user_can_create_customer(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/customers/create');

        $response->assertStatus(200);
    }

    /**
     * Test that user can store a new customer.
     */
    public function test_user_can_store_customer(): void
    {
        $customerData = [
            'name' => 'Test Customer',
            'mobile' => '1234567890',
            'email' => 'customer@example.com',
            'address' => '123 Test Street',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->post('/admin/customers', $customerData);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that customer creation requires valid data.
     */
    public function test_customer_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/customers', []);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test that user can view a customer.
     */
    public function test_user_can_view_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->get("/admin/customers/{$customer->id}");

        $response->assertStatus(200);
    }

    /**
     * Test that user can update a customer.
     */
    public function test_user_can_update_customer(): void
    {
        $customer = Customer::factory()->create();

        $updateData = [
            'name' => 'Updated Customer Name',
            'mobile' => '9876543210',
            'email' => 'updated@example.com',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->put("/admin/customers/{$customer->id}", $updateData);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that user can delete a customer.
     */
    public function test_user_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->delete("/admin/customers/{$customer->id}");

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that customers can be searched.
     */
    public function test_customers_can_be_searched(): void
    {
        Customer::factory()->create(['name' => 'John Doe']);
        Customer::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->user)->get('/admin/customers?search=John');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    /**
     * Test that user can add comment to customer.
     */
    public function test_user_can_add_comment_to_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->post("/admin/customers/{$customer->id}/comments", [
            'comment' => 'Test comment',
        ]);

        $this->assertDatabaseHas('customer_comments', [
            'customer_id' => $customer->id,
            'comment' => 'Test comment',
        ]);

        $response->assertRedirect();
    }
}
