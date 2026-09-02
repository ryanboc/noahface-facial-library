<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\RosterShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_dashboard_shows_schedule_and_leave_balance(): void
    {
        $user = User::factory()->create(['email' => 'alex@example.com', 'role' => 'employee']);
        $employee = $this->employee();
        RosterShift::create(['employee_id' => $employee->id, 'shift_date' => today()->addDay(), 'start_time' => '09:00', 'end_time' => '17:00', 'role' => 'Supervisor']);
        LeaveRequest::create(['employee_id' => $employee->id, 'leave_type' => 'Annual leave', 'start_date' => today()->startOfYear()->addDay(), 'end_date' => today()->startOfYear()->addDays(2), 'status' => 'approved']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('My schedule')
            ->assertSee('Supervisor')
            ->assertSee('18 <span', false);
    }

    public function test_employee_can_request_leave_only_for_themselves(): void
    {
        $user = User::factory()->create(['email' => 'alex@example.com', 'role' => 'employee']);
        $employee = $this->employee();

        $this->actingAs($user)->post(route('profile.leave.store'), [
            'employee_id' => 999,
            'leave_type' => 'Annual leave',
            'start_date' => today()->addWeek()->toDateString(),
            'end_date' => today()->addWeek()->addDay()->toDateString(),
            'reason' => 'Holiday',
        ])->assertRedirect();

        $this->assertDatabaseHas('leave_requests', ['employee_id' => $employee->id, 'status' => 'pending', 'reason' => 'Holiday']);
    }

    public function test_manager_can_approve_but_employee_cannot(): void
    {
        $employeeAccount = User::factory()->create(['role' => 'employee']);
        $manager = User::factory()->create(['role' => 'manager']);
        $leave = LeaveRequest::create(['employee_id' => $this->employee()->id, 'leave_type' => 'Annual leave', 'start_date' => today()->addWeek(), 'end_date' => today()->addWeek(), 'status' => 'pending']);

        $this->actingAs($employeeAccount)->patch(route('leave.review', $leave), ['status' => 'approved'])->assertForbidden();
        $this->actingAs($manager)->patch(route('leave.review', $leave), ['status' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('leave_requests', ['id' => $leave->id, 'status' => 'approved', 'reviewed_by' => $manager->id]);
    }

    public function test_unlinked_account_receives_linking_instructions(): void
    {
        $user = User::factory()->create(['email' => 'unlinked@example.com', 'role' => 'employee']);

        $this->actingAs($user)->get(route('profile.show'))->assertOk()->assertSee('Profile not linked');
    }

    private function employee(): Employee
    {
        return Employee::create([
            'name' => 'Alex Smith', 'email' => 'alex@example.com', 'noahface_id' => 'NF-1',
            'employment_type' => 'Casual', 'base_rate' => 30, 'annual_leave_allowance' => 20,
        ]);
    }
}
