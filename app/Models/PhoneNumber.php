<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'twilio_sid',
        'vapi_assistant_id',
        'is_active'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}