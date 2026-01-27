<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'employee_id', 
        'clock_time', 
        'event_type', 
        'location', 
        'raw_payload'
    ];

    protected $casts = [
        'clock_time' => 'datetime',
        'raw_payload' => 'array',
    ];

    // Link back to Employee so we can access their Award later
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}