<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transcript',
        'summary',
        'recording_url',
        'duration_seconds',
        'cost',
        'assistant_name',
    ];
}
