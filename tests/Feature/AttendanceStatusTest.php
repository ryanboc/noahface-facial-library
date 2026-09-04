<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Award;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_employees_by_their_latest_attendance_event_today(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $award = Award::create(['name' => 'Test Award']);
        $working = $this->employee('Working Person', 'working@example.com', 'NF-1', $award);
        $break = $this->employee('Break Person', 'break@example.com', 'NF-2', $award);
        $finished = $this->employee('Finished Person', 'finished@example.com', 'NF-3', $award);
        $absent = $this->employee('Absent Person', 'absent@example.com', 'NF-4', $award);

        $this->log($working, 'Clock In', 8);
        $this->log($break, 'Clock In', 8);
        $this->log($break, 'Start Break', 10);
        $this->log($finished, 'Clock Out', 17);
        AttendanceLog::create([
            'employee_id' => $absent->id,
            'clock_time' => today()->subDay()->setTime(9, 0),
            'event_type' => 'Clock In',
        ]);

        $this->actingAs($manager)->get(route('attendance.status'))
            ->assertOk()
            ->assertSeeInOrder(['Clocked in', 'Working Person'])
            ->assertSeeInOrder(['On break', 'Break Person'])
            ->assertSeeInOrder(['Clocked out', 'Finished Person'])
            ->assertSeeInOrder(['Not clocked in today', 'Absent Person']);
    }

    private function employee(string $name, string $email, string $noahfaceId, Award $award): Employee
    {
        return Employee::create([
            'name' => $name, 'email' => $email, 'noahface_id' => $noahfaceId,
            'employment_type' => 'Casual', 'base_rate' => 30, 'award_id' => $award->id,
        ]);
    }

    private function log(Employee $employee, string $event, int $hour): void
    {
        AttendanceLog::create([
            'employee_id' => $employee->id,
            'clock_time' => today()->setTime($hour, 0),
            'event_type' => $event,
        ]);
    }
}
