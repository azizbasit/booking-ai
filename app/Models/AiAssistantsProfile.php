<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAssistantsProfile extends Model
{
    use HasFactory;

    protected $table = 'ai_assistants_profiles';

    protected $fillable = [
        'name',
        'specialization',
        'experience',
        'clinic_name',
        'email',
        'business_phone',
        'working_hours_start',
        'working_hours_end',
        'working_days',
        'clinic_address',
        'description',
    ];
}
