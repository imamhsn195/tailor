<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Branch;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    /**
     * Test that a company can be created.
     */
    public function test_company_can_be_created(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $this->assertEquals('Test Company', $company->name);
    }

    /**
     * Test that company can have branches.
     */
    public function test_company_can_have_branches(): void
    {
        $company = Company::factory()->create();
        $branch1 = Branch::factory()->create(['company_id' => $company->id]);
        $branch2 = Branch::factory()->create(['company_id' => $company->id]);

        $this->assertEquals(2, $company->branches->count());
        $this->assertTrue($company->branches->contains($branch1));
        $this->assertTrue($company->branches->contains($branch2));
    }

    /**
     * Test that company settings is cast to array.
     */
    public function test_company_settings_is_array(): void
    {
        $settings = ['currency' => 'USD', 'timezone' => 'UTC'];
        $company = Company::factory()->create(['settings' => $settings]);

        $this->assertIsArray($company->settings);
        $this->assertEquals($settings, $company->settings);
        $this->assertEquals('USD', $company->settings['currency']);
    }

    /**
     * Test that company uses soft deletes.
     */
    public function test_company_uses_soft_deletes(): void
    {
        $company = Company::factory()->create();
        $companyId = $company->id;

        $company->delete();

        $this->assertSoftDeleted('companies', ['id' => $companyId]);
        $this->assertNull(Company::find($companyId));
        $this->assertNotNull(Company::withTrashed()->find($companyId));
    }
}
