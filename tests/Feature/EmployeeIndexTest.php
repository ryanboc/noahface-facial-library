<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_employees_can_be_searched(): void
    {
        $user = User::factory()->create();
        $this->employee('Alex Smith', 'alex@example.com', 'NF-100');
        $this->employee('Jordan Jones', 'jordan@example.com', 'NF-200');

        $this->actingAs($user)
            ->get(route('employees.index', ['search' => 'NF-100']))
            ->assertOk()
            ->assertSee('Alex Smith')
            ->assertDontSee('Jordan Jones');
    }

    public function test_records_per_page_can_be_selected_and_is_preserved_in_links(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 30) as $number) {
            $this->employee("Employee {$number}", "employee{$number}@example.com", "NF-{$number}");
        }

        $this->actingAs($user)
            ->get(route('employees.index', ['per_page' => 25, 'search' => 'Employee']))
            ->assertOk()
            ->assertViewHas('employees', fn ($employees) => $employees->count() === 25 && $employees->perPage() === 25)
            ->assertSee('per_page=25', false)
            ->assertSee('search=Employee', false);
    }

    public function test_invalid_records_per_page_falls_back_to_ten(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('employees.index', ['per_page' => 5000]))
            ->assertOk()
            ->assertViewHas('employees', fn ($employees) => $employees->perPage() === 10);
    }

    public function test_employees_can_be_filtered_by_company(): void
    {
        $user = User::factory()->create();
        $inglewood = Company::where('name', 'Inglewood Farms')->firstOrFail();
        $eden = Company::where('name', 'Eden Farms')->firstOrFail();
        $alex = $this->employee('Alex Smith', 'alex@example.com', 'NF-100');
        $jordan = $this->employee('Jordan Jones', 'jordan@example.com', 'NF-200');
        $alex->companies()->attach($inglewood);
        $jordan->companies()->attach($eden);

        $this->actingAs($user)->get(route('employees.index', ['company_id' => $inglewood->id]))
            ->assertOk()
            ->assertSee('Alex Smith')
            ->assertDontSee('Jordan Jones')
            ->assertViewHas('companyId', $inglewood->id);
    }

    private function employee(string $name, string $email, string $noahfaceId): Employee
    {
        return Employee::create([
            'name' => $name,
            'email' => $email,
            'noahface_id' => $noahfaceId,
            'employment_type' => 'Casual',
            'base_rate' => 30,
        ]);
    }
}
