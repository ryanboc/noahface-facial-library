<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PayrollCalculator
{
    public const WEEKLY_ORDINARY_HOURS = 38;

    public function calculate(Employee $employee, CarbonInterface $periodStart, CarbonInterface $periodEnd): array
    {
        $logs = $employee->attendanceLogs()
            ->whereBetween('clock_time', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
            ->orderBy('clock_time')->get();

        $shifts = $this->completedShifts($logs);
        $weeklyMinutes = [];
        $weeklyOvertimeMinutes = [];
        $lines = [];

        foreach ($shifts as [$start, $end, $breakMinutes]) {
            $paidMinutes = max(0, $start->diffInMinutes($end) - $breakMinutes);
            if (! $paidMinutes) continue;

            $week = $start->copy()->startOfWeek()->toDateString();
            $alreadyWorked = $weeklyMinutes[$week] ?? 0;
            $ordinaryMinutes = min($paidMinutes, max(0, self::WEEKLY_ORDINARY_HOURS * 60 - $alreadyWorked));
            $overtimeMinutes = $paidMinutes - $ordinaryMinutes;
            $weeklyMinutes[$week] = $alreadyWorked + $paidMinutes;

            if ($this->isAustralianPublicHoliday($start)) {
                $this->addLine($lines, $employee, $start, $end, $paidMinutes, 'Public Holiday');
                continue;
            }

            if ($ordinaryMinutes) {
                $details = $employee->getRateDetails($start, $end);
                $this->append($lines, $start, $end, $ordinaryMinutes, $details['label'], $details['final_rate']);
            }
            if ($overtimeMinutes) {
                $overtimeAlready = $weeklyOvertimeMinutes[$week] ?? 0;
                $firstTier = min($overtimeMinutes, max(0, 180 - $overtimeAlready));
                $this->addLine($lines, $employee, $start, $end, $firstTier, 'Overtime');
                if ($overtimeMinutes > $firstTier) {
                    $this->addLine($lines, $employee, $start, $end, $overtimeMinutes - $firstTier, 'Overtime after 3hrs', 'Overtime');
                }
                $weeklyOvertimeMinutes[$week] = $overtimeAlready + $overtimeMinutes;
            }
        }

        return [
            'employee' => $employee,
            'period_start' => Carbon::parse($periodStart),
            'period_end' => Carbon::parse($periodEnd),
            'lines' => collect($lines),
            'total_hours' => collect($lines)->sum('hours'),
            'gross_pay' => collect($lines)->sum('amount'),
        ];
    }

    private function completedShifts(Collection $logs): array
    {
        $shifts = []; $start = null; $breakStart = null; $breakMinutes = 0;
        foreach ($logs as $log) {
            $type = strtolower(preg_replace('/[^a-z0-9]+/i', '', $log->event_type));
            if (in_array($type, ['clockin', 'clockon', 'starttask'], true) && ! $start) {
                $start = $log->clock_time->copy(); $breakStart = null; $breakMinutes = 0;
            } elseif (in_array($type, ['startbreak', 'breakstart', 'breakout'], true) && $start && ! $breakStart) {
                $breakStart = $log->clock_time->copy();
            } elseif (in_array($type, ['endbreak', 'breakin'], true) && $breakStart) {
                $breakMinutes += $breakStart->diffInMinutes($log->clock_time); $breakStart = null;
            } elseif (in_array($type, ['clockout', 'endtask'], true) && $start) {
                if (! $breakStart && $log->clock_time->gt($start)) $shifts[] = [$start, $log->clock_time->copy(), $breakMinutes];
                $start = null; $breakStart = null; $breakMinutes = 0;
            }
        }
        return $shifts;
    }

    private function addLine(array &$lines, Employee $employee, Carbon $start, Carbon $end, int $minutes, string $category, ?string $fallback = null): void
    {
        $raw = $employee->getRate($category) ?? ($fallback ? $employee->getRate($fallback) : null);
        $multiplier = $raw ? ((float) str_replace('%', '', $raw) / 100) : $this->fallbackMultiplier($employee, $category);
        $label = $category.' ('.number_format($multiplier * 100, 0).'%)';
        $this->append($lines, $start, $end, $minutes, $label, ($employee->base_rate ?? 25) * $multiplier);
    }

    private function append(array &$lines, Carbon $start, Carbon $end, int $minutes, string $label, float $rate): void
    {
        $hours = $minutes / 60;
        $lines[] = ['date' => $start->toDateString(), 'clock_in' => $start, 'clock_out' => $end, 'minutes' => $minutes,
            'hours' => $hours, 'rate_label' => $label, 'hourly_rate' => $rate, 'amount' => $hours * $rate];
    }

    private function fallbackMultiplier(Employee $employee, string $category): float
    {
        return match ($category) {
            'Public Holiday' => str_contains($employee->employment_type, 'Casual') ? 2.0 : 2.5,
            'Overtime after 3hrs' => 2.0,
            'Overtime' => 1.5,
            default => 1.0,
        };
    }

    public function isAustralianPublicHoliday(CarbonInterface $date): bool
    {
        $date = Carbon::parse($date); $year = $date->year;
        $easter = Carbon::createFromTimestamp(easter_date($year), config('app.timezone'))->startOfDay();
        $fixed = ["{$year}-01-01", "{$year}-01-26", "{$year}-04-25", "{$year}-12-25", "{$year}-12-26"];
        $holidays = collect($fixed)->map(fn ($day) => Carbon::parse($day));
        $holidays->push($easter->copy()->subDays(2), $easter->copy()->addDay());
        foreach ($holidays->take(5) as $holiday) {
            if ($holiday->isWeekend()) {
                $observed = $holiday->copy()->next(CarbonInterface::MONDAY);
                while ($holidays->contains(fn ($item) => $item->isSameDay($observed))) $observed->addDay();
                $holidays->push($observed);
            }
        }
        return $holidays->contains(fn ($holiday) => $holiday->isSameDay($date));
    }
}
