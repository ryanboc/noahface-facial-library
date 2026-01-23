<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwardCondition extends Model
{
    protected $fillable = [
        'award_id', 'hours_per_day_rule', 'leading_hand_allowance',
        'meal_allowance', 'paid_break_rule', 'unpaid_break_rule', 'remarks'
    ];
}