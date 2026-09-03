<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosterShift extends Model
{
    protected $fillable = ['employee_id', 'company_id', 'shift_date', 'start_time', 'end_time', 'location', 'role', 'notes', 'status'];

    protected function casts(): array
    {
        return ['shift_date' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
