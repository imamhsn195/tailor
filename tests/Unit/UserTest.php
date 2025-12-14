<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Test that a user can be created.
     */
    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    /**
     * Test that user password is hashed.
     */
    public function test_user_password_is_hashed(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
        ]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(\Hash::check($password, $user->password));
    }

    /**
     * Test that user can have branches.
     */
    public function test_user_can_have_branches(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();

        $user->branches()->attach($branch->id, ['is_default' => true]);

        $this->assertTrue($user->branches->contains($branch));
        $this->assertEquals(1, $user->branches->count());
    }

    /**
     * Test that user can have a default branch.
     */
    public function test_user_can_have_default_branch(): void
    {
        $user = User::factory()->create();
        $defaultBranch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();

        $user->branches()->attach($defaultBranch->id, ['is_default' => true]);
        $user->branches()->attach($otherBranch->id, ['is_default' => false]);

        $this->assertEquals($defaultBranch->id, $user->defaultBranch()->id);
    }

    /**
     * Test that user is_active is cast to boolean.
     */
    public function test_user_is_active_is_boolean(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    /**
     * Test that user email_verified_at is cast to datetime.
     */
    public function test_user_email_verified_at_is_datetime(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\DateTimeInterface::class, $user->email_verified_at);
    }

    /**
     * Test that user can have login history.
     */
    public function test_user_can_have_login_history(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->loginHistory());
    }
}
