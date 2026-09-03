<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_displays_only_approved_leave(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $approvedEmployee = $this->employee('Alex Smith', 'alex@example.com', 'NF-1');
        $pendingEmployee = $this->employee('Jordan Jones', 'jordan@example.com', 'NF-2');
        LeaveRequest::create(['employee_id' => $approvedEmployee->id, 'leave_type' => 'Annual leave', 'start_date' => today(), 'end_date' => today()->addDay(), 'status' => 'approved']);
        LeaveRequest::create(['employee_id' => $pendingEmployee->id, 'leave_type' => 'Personal leave', 'start_date' => today(), 'end_date' => today(), 'status' => 'pending']);

        $this->actingAs($manager)->get(route('leave.calendar', ['month' => today()->format('Y-m')]))
            ->assertOk()
            ->assertSee('Approved leave calendar')
            ->assertSee('Alex Smith')
            ->assertDontSee('Jordan Jones');
    }

    public function test_calendar_can_be_filtered_by_company(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $inglewood = Company::where('name', 'Inglewood Farms')->firstOrFail();
        $eden = Company::where('name', 'Eden Farms')->firstOrFail();
        $alex = $this->employee('Alex Smith', 'alex@example.com', 'NF-1');
        $jordan = $this->employee('Jordan Jones', 'jordan@example.com', 'NF-2');
        $alex->companies()->attach($inglewood);
        $jordan->companies()->attach($eden);
        foreach ([$alex, $jordan] as $employee) {
            LeaveRequest::create(['employee_id' => $employee->id, 'leave_type' => 'Annual leave', 'start_date' => today(), 'end_date' => today(), 'status' => 'approved']);
        }

        $this->actingAs($manager)->get(route('leave.calendar', ['month' => today()->format('Y-m'), 'company_id' => $inglewood->id]))
            ->assertOk()
            ->assertSee('Alex Smith')
            ->assertDontSee('Jordan Jones');
    }

    public function test_employee_role_cannot_access_leave_calendar(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)->get(route('leave.calendar'))->assertForbidden();
    }

    private function employee(string $name, string $email, string $noahfaceId): Employee
    {
        return Employee::create([
            'name' => $name, 'email' => $email, 'noahface_id' => $noahfaceId,
            'employment_type' => 'Casual', 'base_rate' => 30,
        ]);
    }
}
