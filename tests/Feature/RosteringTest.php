<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\RosterShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosteringTest extends TestCase
{
    use RefreshDatabase;

    private function employee(): Employee
    {
        return Employee::create(['name' => 'Alex Smith', 'email' => 'alex@example.com', 'noahface_id' => 'NF-1', 'employment_type' => 'Casual', 'base_rate' => 30]);
    }

    public function test_manager_can_submit_and_approve_leave(): void
    {
        $manager = User::factory()->create(); $employee = $this->employee();
        $this->actingAs($manager)->post(route('leave.store'), ['employee_id' => $employee->id, 'leave_type' => 'Annual leave', 'start_date' => '2026-09-07', 'end_date' => '2026-09-09'])->assertRedirect();
        $leave = LeaveRequest::firstOrFail();
        $this->actingAs($manager)->patch(route('leave.review', $leave), ['status' => 'approved', 'manager_note' => 'Approved'])->assertRedirect();
        $this->assertDatabaseHas('leave_requests', ['id' => $leave->id, 'status' => 'approved', 'reviewed_by' => $manager->id]);
    }

    public function test_shift_cannot_be_added_during_approved_leave(): void
    {
        $manager = User::factory()->create(); $employee = $this->employee();
        LeaveRequest::create(['employee_id' => $employee->id, 'leave_type' => 'Annual leave', 'start_date' => '2026-09-07', 'end_date' => '2026-09-09', 'status' => 'approved']);
        $this->actingAs($manager)->from(route('roster.index'))->post(route('roster.store'), ['employee_id' => $employee->id, 'shift_date' => '2026-09-08', 'start_time' => '09:00', 'end_time' => '17:00'])->assertSessionHasErrors('employee_id');
        $this->assertDatabaseCount('roster_shifts', 0);
    }

    public function test_roster_can_be_printed(): void
    {
        $manager = User::factory()->create(); $employee = $this->employee();
        RosterShift::create(['employee_id' => $employee->id, 'shift_date' => '2026-09-08', 'start_time' => '09:00', 'end_time' => '17:00', 'role' => 'Supervisor']);
        LeaveRequest::create(['employee_id' => $employee->id, 'leave_type' => 'Annual leave', 'start_date' => '2026-09-09', 'end_date' => '2026-09-09', 'status' => 'approved']);
        $this->actingAs($manager)->get(route('roster.print', ['week' => '2026-09-07']))->assertOk()->assertSee('Rosters for week commencing')->assertSee('Alex Smith')->assertSee('Supervisor')->assertSee('Annual leave');
    }

    public function test_employee_cannot_be_rostered_twice_on_the_same_day(): void
    {
        $manager = User::factory()->create(); $employee = $this->employee();
        RosterShift::create(['employee_id' => $employee->id, 'shift_date' => '2026-09-08', 'start_time' => '09:00', 'end_time' => '17:00']);

        $this->actingAs($manager)->from(route('roster.index'))->post(route('roster.store'), [
            'employee_id' => $employee->id, 'shift_date' => '2026-09-08', 'start_time' => '18:00', 'end_time' => '22:00',
        ])->assertSessionHasErrors(['employee_id' => 'Alex Smith is already rostered on 08 Sep 2026.']);

        $this->assertDatabaseCount('roster_shifts', 1);
    }

    public function test_shift_cannot_take_employee_over_forty_hours_for_the_week(): void
    {
        $manager = User::factory()->create(); $employee = $this->employee();
        foreach (range(7, 10) as $day) {
            RosterShift::create(['employee_id' => $employee->id, 'shift_date' => "2026-09-{$day}", 'start_time' => '09:00', 'end_time' => '19:00']);
        }

        $this->actingAs($manager)->from(route('roster.index'))->post(route('roster.store'), [
            'employee_id' => $employee->id, 'shift_date' => '2026-09-11', 'start_time' => '09:00', 'end_time' => '10:00',
        ])->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('roster_shifts', 4);
    }

    public function test_shift_above_thirty_eight_hours_is_added_with_warning(): void
    {
        $manager = User::factory()->create(); $employee = $this->employee();
        foreach (range(7, 10) as $day) {
            RosterShift::create(['employee_id' => $employee->id, 'shift_date' => "2026-09-{$day}", 'start_time' => '09:00', 'end_time' => '18:30']);
        }

        $this->actingAs($manager)->post(route('roster.store'), [
            'employee_id' => $employee->id, 'shift_date' => '2026-09-11', 'start_time' => '09:00', 'end_time' => '10:00',
        ])->assertSessionHas('success', fn ($message) => str_contains($message, 'above the standard 38 hours'));

        $this->assertDatabaseCount('roster_shifts', 5);
    }
}
