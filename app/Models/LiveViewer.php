<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveViewer extends Model
{
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'live_id',
    ];

    protected $hidden = [
        'joined_at',
        'left_at',
    ];
}
