<?php

use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;



//Route::post('/store-appointment', [AppointmentController::class, 'store'])->name('store-appointment');
Route::post('/assistant-calls', [AiAssistantController::class, 'physioAssistantEvent'])->name('assistant-calls');
