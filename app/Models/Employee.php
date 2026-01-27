<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['name', 'email', 'base_rate','noahface_id', 'award_id', 'employment_type'];

    // Link to the Award (Rules)
    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }

    // Link to the Time Logs (Data)
    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Helper to get the specific rate for this employee for a specific scenario.
     * Usage: $employee->getRate('Public Holiday'); // Returns '250%'
     */
    public function getRate(string $category)
    {
        return $this->award->rates()
            ->where('employment_type', $this->employment_type)
            ->where('category', $category)
            ->value('rate_value');
    }

    /**
     * Smart wrapper: You give it a date, it figures out the category and returns the rate.
     * Usage: $employee->getRateForDate($log->clock_time);
     */
    public function getRateForDate($date)
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        
        // 1. Default to the day of the week (e.g., "Monday", "Sunday")
        $category = $carbonDate->format('l');

        // 2. (Optional) Check for Public Holidays here
        // $isHoliday = ... check your holidays table ...
        // if ($isHoliday) { $category = 'Public Holiday'; }

        // 3. Call your existing function with the correct category
        // Note: This relies on your database having categories like "Monday", "Saturday", etc.
        $rate = $this->getRate($category);

        // 4. If we found a rate, return it. If not, return a fallback.
        return $rate ?? 'Ordinary (Base)';
    }

    public function getRateDetails($date)
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        $dayName = $carbonDate->format('l'); // e.g., "Monday", "Saturday"

        // 1. Try to find a rule matching the exact Day Name (e.g., "Saturday", "Sunday")
        // Your seeder currently has entries for 'Saturday' and 'Sunday'.
        $rule = $this->award->rates()
            ->where('employment_type', $this->employment_type) // e.g. 'Casual'
            ->where('category', $dayName)
            ->first();

        // 2. If no specific day rule is found (e.g., it's a Monday-Friday), 
        // we need to decide on a default.
        // Since your seeder doesn't have "Ordinary" rows yet, we will fallback to a default.
        if (!$rule) {
            // Default Logic:
            // If they are Casual, they usually get 25% loading on base (125%).
            // If Full Time, they get base (100%).
            $isCasual = str_contains($this->employment_type, 'Casual');
            
            return [
                'label' => $isCasual ? 'Ordinary (Casual 25%)' : 'Ordinary (Base)',
                'multiplier' => $isCasual ? 1.25 : 1.0, 
                'final_rate' => ($this->base_rate ?? 25.00) * ($isCasual ? 1.25 : 1.0)
            ];
        }

        // 3. PARSE THE STRING (The important fix for your database)
        // Converts "120%" -> 1.20 or "200%" -> 2.00
        $rawString = $rule->rate_value; // e.g. "120%"
        $numericValue = floatval(str_replace('%', '', $rawString)); // 120
        $multiplier = $numericValue / 100; // 1.20

        // 4. Calculate Money
        // We default to $25.00 if you haven't set a base_rate for the employee yet
        $baseRate = $this->base_rate ?? 25.00;

        return [
            'label' => "{$rule->category} ({$rawString})", // e.g., "Saturday (120%)"
            'multiplier' => $multiplier,
            'final_rate' => $baseRate * $multiplier
        ];
    }
}