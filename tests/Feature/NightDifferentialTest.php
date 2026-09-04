<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NightDifferentialTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_weekday_night_shift_uses_the_casual_night_differential(): void
    {
        $employee = $this->employeeWithRates('Casual', [
            'Night' => '140%',
        ]);

        $rate = $employee->getRateDetails('2026-09-07 22:00:00', '2026-09-08 06:00:00');

        $this->assertSame('Night (140%)', $rate['label']);
        $this->assertSame(1.4, $rate['multiplier']);
        $this->assertSame(42.0, $rate['final_rate']);
    }

    public function test_a_weekday_night_shift_uses_the_permanent_employee_rate(): void
    {
        $employee = $this->employeeWithRates('Full Time/Part Time', [
            'Night' => '115%',
        ]);

        $rate = $employee->getRateDetails('2026-09-07 22:00:00', '2026-09-08 08:00:00');

        $this->assertSame('Night (115%)', $rate['label']);
        $this->assertSame(34.5, $rate['final_rate']);
    }

    public function test_a_weekday_afternoon_shift_uses_the_same_differential(): void
    {
        $employee = $this->employeeWithRates('Casual', [
            'Night' => '140%',
        ]);

        $rate = $employee->getRateDetails('2026-09-04 16:51:00', '2026-09-04 22:01:00');

        $this->assertSame('Night (140%)', $rate['label']);
        $this->assertSame(42.0, $rate['final_rate']);
    }

    public function test_a_shift_finishing_after_the_night_cutoff_uses_the_ordinary_rate(): void
    {
        $employee = $this->employeeWithRates('Full Time/Part Time', [
            'Night' => '115%',
        ]);

        $rate = $employee->getRateDetails('2026-09-07 22:00:00', '2026-09-08 08:01:00');

        $this->assertSame('Ordinary (Base)', $rate['label']);
        $this->assertSame(30.0, $rate['final_rate']);
    }

    public function test_weekend_rate_takes_precedence_over_night_differential(): void
    {
        $employee = $this->employeeWithRates('Casual', [
            'Night' => '140%',
            'Saturday' => '150%',
        ]);

        $rate = $employee->getRateDetails('2026-09-05 22:00:00', '2026-09-06 06:00:00');

        $this->assertSame('Saturday (150%)', $rate['label']);
        $this->assertSame(45.0, $rate['final_rate']);
    }

    private function employeeWithRates(string $employmentType, array $rates): Employee
    {
        $award = Award::create(['name' => 'Test Award']);

        foreach ($rates as $category => $rateValue) {
            $award->rates()->create([
                'employment_type' => $employmentType,
                'category' => $category,
                'rate_value' => $rateValue,
            ]);
        }

        return Employee::create([
            'name' => 'Night Worker',
            'email' => strtolower(str_replace([' ', '/'], '-', $employmentType)).'@example.com',
            'noahface_id' => 'NF-'.md5($employmentType),
            'employment_type' => $employmentType,
            'base_rate' => 30,
            'award_id' => $award->id,
        ]);
    }
}
