<?php

namespace Tests\Feature;

use App\Mail\PayslipMail;
use App\Models\AttendanceLog;
use App\Models\Award;
use App\Models\Employee;
use App\Models\User;
use App\Services\PayrollCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PayslipTest extends TestCase
{
    use RefreshDatabase;

    public function test_breaks_are_excluded_and_public_holiday_rate_is_applied(): void
    {
        $employee = $this->employee();
        foreach ([['Clock In','2026-01-26 08:00'], ['Start Break','2026-01-26 12:00'], ['End Break','2026-01-26 12:30'], ['Clock Out','2026-01-26 16:30']] as [$type,$time]) {
            AttendanceLog::create(['employee_id'=>$employee->id, 'event_type'=>$type, 'clock_time'=>$time]);
        }
        $pay = app(PayrollCalculator::class)->calculate($employee, Carbon::parse('2026-01-26'), Carbon::parse('2026-01-26'));
        $this->assertEquals(8.0, $pay['total_hours']);
        $this->assertEquals(600.0, $pay['gross_pay']);
        $this->assertStringContainsString('Public Holiday', $pay['lines']->first()['rate_label']);
    }

    public function test_management_can_bulk_email_payslips(): void
    {
        Mail::fake(); $employee = $this->employee();
        $user = User::factory()->create(['role' => 'manager']);
        $this->actingAs($user)->post(route('payslips.email'), ['employee_ids'=>[$employee->id], 'start_date'=>'2026-01-01', 'end_date'=>'2026-01-07'])->assertRedirect();
        Mail::assertSent(PayslipMail::class, 1);
    }

    private function employee(): Employee
    {
        $award = Award::create(['name'=>'Test']);
        foreach (['Public Holiday'=>'250%', 'Overtime'=>'150%', 'Overtime after 3hrs'=>'200%', 'Night'=>'115%'] as $category=>$rate) $award->rates()->create(['employment_type'=>'Full Time/Part Time','category'=>$category,'rate_value'=>$rate]);
        return Employee::create(['name'=>'Employee','email'=>'employee@example.com','noahface_id'=>'pay-1','employment_type'=>'Full Time/Part Time','base_rate'=>30,'award_id'=>$award->id]);
    }
}
