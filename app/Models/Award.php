<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Award extends Model
{
    protected $fillable = ['name', 'pay_guide_link'];

    // An award has one set of text-based conditions
    public function conditions(): HasOne
    {
        return $this->hasOne(AwardCondition::class);
    }

    // An award has many numeric rates (Casual/OT/etc)
    public function rates(): HasMany
    {
        return $this->hasMany(AwardRate::class);
    }
}
