<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Concerns\WithTenant;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up tenant context
        $this->setUpTenant();
        
        // Create permissions
        Permission::create(['name' => 'product.view']);
        Permission::create(['name' => 'product.create']);
        Permission::create(['name' => 'product.edit']);
        Permission::create(['name' => 'product.delete']);

        // Create role and assign permissions
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['product.view', 'product.create', 'product.edit', 'product.delete']);

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
     * Test that products index page requires authentication.
     */
    public function test_products_index_requires_authentication(): void
    {
        $response = $this->get('/admin/products');

        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated user can view products index.
     */
    public function test_authenticated_user_can_view_products_index(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/products');

        // Just verify it doesn't redirect (view might have issues)
        $response->assertStatus(200);
    }

    /**
     * Test that user can create a product.
     */
    public function test_user_can_create_product(): void
    {
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();

        $response = $this->actingAs($this->user)->get('/admin/products/create');

        $response->assertStatus(200);
    }

    /**
     * Test that user can store a new product.
     */
    public function test_user_can_store_product(): void
    {
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->post('/admin/products', $productData);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that product creation requires valid data.
     */
    public function test_product_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/products', []);

        $response->assertSessionHasErrors(['name', 'category_id', 'unit_id']);
    }

    /**
     * Test that user can view a product.
     */
    public function test_user_can_view_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->get("/admin/products/{$product->id}");

        $response->assertStatus(200);
    }

    /**
     * Test that user can update a product.
     */
    public function test_user_can_update_product(): void
    {
        $product = Product::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();

        $updateData = [
            'name' => 'Updated Product Name',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 120.00,
            'sale_price' => 180.00,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->put("/admin/products/{$product->id}", $updateData);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that user can delete a product.
     */
    public function test_user_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->delete("/admin/products/{$product->id}");

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that products can be searched.
     */
    public function test_products_can_be_searched(): void
    {
        Product::factory()->create(['name' => 'Test Product']);
        Product::factory()->create(['name' => 'Another Product']);

        $response = $this->actingAs($this->user)->get('/admin/products?search=Test');

        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertDontSee('Another Product');
    }
}
