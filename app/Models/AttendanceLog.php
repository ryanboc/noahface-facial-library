<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = ['employee_id', 'clock_time', 'event_type', 'location', 'raw_payload'];

    protected $casts = [
        'clock_time' => 'datetime',
        'raw_payload' => 'array',
    ];
}