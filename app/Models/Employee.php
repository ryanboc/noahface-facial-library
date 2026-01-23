<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['name', 'email', 'noahface_id', 'award_id', 'employment_type'];

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
}