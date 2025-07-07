<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTopupTransaction extends Model
{
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'wallet_id',
        'coin_amount',
        'price',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
}
