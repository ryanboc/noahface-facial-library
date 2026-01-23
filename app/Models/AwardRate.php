<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwardRate extends Model
{
    protected $fillable = ['award_id', 'employment_type', 'category', 'rate_value'];
}