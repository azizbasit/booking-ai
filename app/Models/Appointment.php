<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'full_name',
        'user_id',
        'phone_number',
        'service_type',
        'appointment_date',
        'appointment_slot',
        'start_time',
        'end_time',
        'status',  
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
