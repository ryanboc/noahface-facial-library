<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_five_default_companies_are_created(): void
    {
        foreach (['Inglewood Farms', 'Country Synergy', 'Country Heritage Feeds', 'Next Office', 'Eden Farms'] as $name) {
            $this->assertDatabaseHas('companies', ['name' => $name]);
        }
    }

    public function test_manager_can_manage_companies(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->post(route('companies.store'), [
            'name' => 'New Workplace', 'address' => '1 Main Street', 'notes' => 'Test location',
        ])->assertRedirect(route('companies.index'));

        $company = Company::where('name', 'New Workplace')->firstOrFail();
        $this->actingAs($manager)->put(route('companies.update', $company), ['name' => 'Updated Workplace'])->assertRedirect(route('companies.index'));
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Updated Workplace']);

        $this->actingAs($manager)->delete(route('companies.destroy', $company))->assertRedirect(route('companies.index'));
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_employee_can_be_assigned_to_multiple_companies(): void
    {
        $employee = Employee::create([
            'name' => 'Alex Smith', 'email' => 'alex@example.com', 'noahface_id' => 'NF-1',
            'employment_type' => 'Casual', 'base_rate' => 30,
        ]);
        $companies = Company::whereIn('name', ['Inglewood Farms', 'Eden Farms'])->get();
        $employee->companies()->sync($companies->modelKeys());

        $this->assertEqualsCanonicalizing(['Eden Farms', 'Inglewood Farms'], $employee->companies()->pluck('name')->all());
    }

    public function test_employee_role_cannot_manage_companies(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)->get(route('companies.index'))->assertForbidden();
    }
}
