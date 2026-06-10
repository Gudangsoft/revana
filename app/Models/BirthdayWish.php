<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayWish extends Model
{
    protected $fillable = [
        'sender_type',
        'sender_id',
        'sender_name',
        'recipient_type',
        'recipient_id',
        'recipient_name',
        'message',
        'wish_year',
    ];

    protected $casts = [
        'wish_year' => 'integer',
    ];
}
