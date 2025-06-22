<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\CallSummaryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin-page', function () {
    return view('admin.index');
})->name('dashboard');

Route::get('/calendar', [CalendarController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('calendar');

Route::post('/initiate-call', [App\Http\Controllers\VapiCallController::class, 'initiateCall'])->name('initiate.call');
Route::get('/call-monitor', function () {
    return view('admin.call-monitor');
})->name('call.monitor');

Route::middleware('auth')->group(function () {
    // Route::get('admin-page', function () {
    //     return view('admin.index');
    // });
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments');

    // Add these new routes for call summaries and recordings

});

Route::post('/ai-assistants-profiles', [AiAssistantController::class, 'store'])->name('ai-assistants-profiles.store');

Route::get('/test-mail', function () {
    try {
        Mail::raw('Test mail content', function ($message) {
            $message->to('ahmedhussain50451122@gmail.com')
                ->subject('Test Mail');
        });
        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Add this line with your other admin routes
Route::get('/call-summaries', [CallSummaryController::class, 'index'])->name('call-summaries');

// Add route for streaming audio files
Route::get('/recordings/{filename}', function ($filename) {
    $path = 'recordings/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return response()->file(storage_path('app/public/' . $path));
})->name('recording.stream');

require __DIR__ . '/auth.php';
