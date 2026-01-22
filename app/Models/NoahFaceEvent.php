<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoahFaceEvent extends Model
{
    use HasFactory;

    protected $table = 'noahface_events';

    protected $fillable = [
        'eventid',
        'utc',
        'time',
        'org',
        'site',
        'device',
        'devid',
        'type',
        'detail',
        'method',
        'userid',
        'number',
        'firstname',
        'lastname',
        'cardnum',
        'latitude',
        'longitude',
        'altitude',
        'accuracy',
        'temperature',
        'elevated',
        'timing',
        'sentiment',
        'usertype',
    ];

    protected $casts = [
        'utc' => 'datetime',
        'time' => 'datetime',
        'elevated' => 'boolean',
    ];
}
