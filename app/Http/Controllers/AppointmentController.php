<?php

namespace App\Http\Controllers;

use App\Models\Appointment;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = Auth::user();   

        $appointments = Appointment::where('user_id', $user->id)->get();
        // $appointments = Appointment::all();


        return view('admin.appointments', compact('appointments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $appointmentData = $data['message']['toolCalls'][0]['function']['arguments'];

        // Convert date and time into correct format
        $formattedDate = Carbon::parse($appointmentData['appointment_date'])->format('Y-m-d');
        $formattedTime = Carbon::parse($appointmentData['appointment_slot'])->format('H:i');

        
        $appointmentData['appointment_date'] = $formattedDate;
        $appointmentData['appointment_slot'] = $formattedTime;
        $appointmentData['start_time'] = $formattedTime;
        $endTime = Carbon::parse($formattedTime)->addHour()->format('H:i');
        $appointmentData['end_time'] = $endTime;
        $appointmentData['user_id'] = 2;  
        $appointmentData['status'] = 'pending'; 
        
        $validatedData = validator($appointmentData, [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'service_type' => 'required|string|max:100',
            'appointment_date' => 'string',
            'appointment_slot' => 'string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'user_id' => 'required|integer|exists:users,id',
            'status' => 'string|in:pending,confirmed,cancelled',
            'notes' => 'string|nullable',
        ])->validate();

        Log::info('Validated Appointment Data:', $validatedData);


        $appointment = Appointment::create($validatedData);

        return response()->json([
            'message' => 'Appointment saved successfully',
            'data' => $appointment,
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
