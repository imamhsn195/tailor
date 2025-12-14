<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Order;
use Tests\TestCase;

class BranchTest extends TestCase
{
    /**
     * Test that a branch can be created.
     */
    public function test_branch_can_be_created(): void
    {
        $branch = Branch::factory()->create([
            'name' => 'Test Branch',
            'email' => 'branch@example.com',
        ]);

        $this->assertDatabaseHas('branches', [
            'name' => 'Test Branch',
            'email' => 'branch@example.com',
        ]);

        $this->assertEquals('Test Branch', $branch->name);
    }

    /**
     * Test that branch can have users.
     */
    public function test_branch_can_have_users(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create();

        $branch->users()->attach($user->id, ['is_default' => true]);

        $this->assertTrue($branch->users->contains($user));
        $this->assertEquals(1, $branch->users->count());
    }

    /**
     * Test that branch can have orders.
     */
    public function test_branch_can_have_orders(): void
    {
        $branch = Branch::factory()->create();
        $order = Order::factory()->create(['branch_id' => $branch->id]);

        $this->assertTrue($branch->orders->contains($order));
        $this->assertEquals(1, $branch->orders->count());
    }

    /**
     * Test that branch modules is cast to array.
     */
    public function test_branch_modules_is_array(): void
    {
        $modules = ['pos', 'inventory', 'orders'];
        $branch = Branch::factory()->create(['modules' => $modules]);

        $this->assertIsArray($branch->modules);
        $this->assertEquals($modules, $branch->modules);
    }

    /**
     * Test that branch is_active is cast to boolean.
     */
    public function test_branch_is_active_is_boolean(): void
    {
        $branch = Branch::factory()->create(['is_active' => true]);

        $this->assertIsBool($branch->is_active);
        $this->assertTrue($branch->is_active);
    }

    /**
     * Test that branch uses soft deletes.
     */
    public function test_branch_uses_soft_deletes(): void
    {
        $branch = Branch::factory()->create();
        $branchId = $branch->id;

        $branch->delete();

        $this->assertSoftDeleted('branches', ['id' => $branchId]);
        $this->assertNull(Branch::find($branchId));
        $this->assertNotNull(Branch::withTrashed()->find($branchId));
    }
}
