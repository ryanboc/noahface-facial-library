<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Award;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetCompanyFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_timesheet_only_shows_employees_from_selected_company(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $award = Award::create(['name' => 'Test Award']);
        $inglewood = Company::where('name', 'Inglewood Farms')->firstOrFail();
        $eden = Company::where('name', 'Eden Farms')->firstOrFail();
        $alex = $this->employee('Alex Smith', 'alex@example.com', 'NF-1', $award);
        $jordan = $this->employee('Jordan Jones', 'jordan@example.com', 'NF-2', $award);
        $alex->companies()->attach($inglewood);
        $jordan->companies()->attach($eden);
        $this->clockedShift($alex);
        $this->clockedShift($jordan);

        $this->actingAs($manager)->get(route('attendance.timesheet', [
            'company_id' => $inglewood->id,
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]))->assertOk()
            ->assertSee('Inglewood Farms')
            ->assertSee('Alex Smith')
            ->assertDontSee('Jordan Jones');
    }

    private function employee(string $name, string $email, string $noahfaceId, Award $award): Employee
    {
        return Employee::create([
            'name' => $name, 'email' => $email, 'noahface_id' => $noahfaceId,
            'employment_type' => 'Casual', 'base_rate' => 30, 'award_id' => $award->id,
        ]);
    }

    private function clockedShift(Employee $employee): void
    {
        AttendanceLog::create(['employee_id' => $employee->id, 'clock_time' => today()->setTime(9, 0), 'event_type' => 'Clock In']);
        AttendanceLog::create(['employee_id' => $employee->id, 'clock_time' => today()->setTime(17, 0), 'event_type' => 'Clock Out']);
    }
}
