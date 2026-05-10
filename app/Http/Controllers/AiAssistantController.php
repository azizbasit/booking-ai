<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\AiAssistantsProfile;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\CallSummary;
use App\Models\PhoneNumber;
use Illuminate\Support\Facades\Storage;
use App\Models\User;


class AiAssistantController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string',
            'experience' => 'nullable|integer',
            'clinic_name' => 'nullable|string',
            'email' => 'nullable|email',
            'business_phone' => 'nullable|string',
            'working_hours_start' => 'nullable|string',
            'working_hours_end' => 'nullable|string',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'clinic_street' => 'required|string',
            'clinic_city' => 'required|string',
            'clinic_region' => 'required|string',
            'clinic_postal_code' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Step 1: Purchase Twilio Number first
        $numberResponse = $this->purchaseTwilioNumber([
            'clinic_name' => $data['clinic_name'],
            'clinic_street' => $data['clinic_street'],
            'clinic_city' => $data['clinic_city'],
            'clinic_region' => $data['clinic_region'],
            'clinic_postal_code' => $data['clinic_postal_code']
        ], "Dr. {$data['name']}", $data['clinic_name']);

        if (!$numberResponse['success']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to purchase phone number: ' . $numberResponse['error']
            ], 400);
        }

        // Step 2: Prepare data for assistant profile
        $data['clinic_address'] = implode(', ', [
            $data['clinic_street'],
            $data['clinic_city'],
            $data['clinic_region'],
            $data['clinic_postal_code']
        ]);

        unset($data['clinic_street'], $data['clinic_city'], $data['clinic_region'], $data['clinic_postal_code']);
        $data['working_days'] = implode(',', $data['working_days']);
        $data['twilio_phone_number'] = $numberResponse['phone_number'];

        // Step 3: Create AI Assistant Profile
        $assistantProfile = AiAssistantsProfile::create($data);

        // Step 4: Create VAPI Assistant
        $vapiResponse = $this->createVapiAssistant($data);
        if ($vapiResponse === false) {
            $this->cleanupOnFailure([
                'twilio_sid' => $numberResponse['twilio_sid'],
                'profile_id' => $assistantProfile->id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create assistant'
            ], 400);
        }

        $vapiData = json_decode($vapiResponse, true);
        if (!isset($vapiData['id'])) {
            $this->cleanupOnFailure([
                'twilio_sid' => $numberResponse['twilio_sid'],
                'profile_id' => $assistantProfile->id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid VAPI response'
            ], 400);
        }

        // Step 5: Assign number to VAPI
        $assignResponse = $this->assignNumberToVapi($numberResponse, $vapiData['id'], $data['name']);
        if (!$assignResponse['success']) {
            $this->cleanupOnFailure([
                'twilio_sid' => $numberResponse['twilio_sid'],
                'profile_id' => $assistantProfile->id,
                'assistant_id' => $vapiData['id']
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to assign number: ' . $assignResponse['error']
            ], 400);
        }

        // Step 6: Create phone number record
        PhoneNumber::create([
            'user_id' => Auth::id(),
            'phone_number' => $numberResponse['phone_number'],
            'twilio_sid' => $numberResponse['twilio_sid'],
            'vapi_assistant_id' => $vapiData['id'],
            'address_sid' => $numberResponse['address_sid']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Assistant created and phone number assigned successfully!',
            'phone_number' => $numberResponse['phone_number'],
            'assistant_id' => $vapiData['id']
        ]);
    }

    private function createVapiAssistant($data)
    {
        $vapiApiKey = config('services.vapi.api_key');
        Log::info('VAPI API Key: ' . $vapiApiKey);
        $user = Auth::user();
        $physioId = $user ? $user->id : null;

        // Prepare variables from $data
        $clinicName = $data['clinic_name'] ?? '';
        $therapistName = $data['name'] ?? '';
        $specialization = $data['specialization'] ?? '';
        $experience = $data['experience'] ?? '';
        $businessPhone = $data['business_phone'] ?? '';
        $email = $data['email'] ?? '';
        $workingHours = ($data['working_hours_start'] ?? '') . ' - ' . ($data['working_hours_end'] ?? '');
        $workingDays = $data['working_days'] ?? '';
        $clinicAddress = $data['clinic_address'] ?? '';
        $description = $data['description'] ?? '';


        $systemPrompt = <<<EOD
[Identity]
You are Emma, a smart, friendly voice assistant working for {$clinicName}, representing Dr. {$therapistName}, a professional physiotherapist specializing in {$specialization}, with {$experience} years of experience in treating patients.

[your Job]
Your job is to handle incoming calls on behalf of Dr. {$therapistName}, assist callers with:
- General questions about services
- Booking appointments
- Cancelling appointments
You sound natural and polite, like a real human receptionist. You never mention that you're an AI.

[Key details to personalize]
- Physio ID: {$physioId}
- Clinic Name: {$clinicName}
- Physiotherapist's Full Name: {$therapistName}
- Area of Specialization: {$specialization}
- Total Years of Experience: {$experience}
- Business Phone Number: {$businessPhone}
- Business Email Address: {$email}
- Working Hours: {$workingHours}
- Working Days: {$workingDays}
- Full Clinic Address: {$clinicAddress}
- Description about the service: {$description}

[Current Date & Time]
Today is {{ "now" | date: "%B %d, %Y, %I:%M %p", "Asia/Karachi" }}. keep in mind the current date and time when processing requests.

[Strict Booking Time Rules]
- Bookings must be for future slots only.
- Today: Accept only slots after the current hour, rounded to the next full hour (e.g., 9:12 AM → earliest is 10:00 AM).
- Future dates: Accept only if the selected time is within {$workingHours}.
- Bookings are allowed only on {$workingDays}. If the caller requests a non-working day, politely inform them and suggest an alternative.
- Reject all bookings for past hours or the current hour.

[Strict Availability Check Rule]
Always call the physio_check_availability tool before booking any appointment, no matter how many times the user changes the time — booking must only proceed after a valid available slot is confirmed.

[Response Guidelines]
- Use a professional yet approachable tone — no casual or vague language.  
- Keep responses short and focused.
- Do not answer diagnosis or medical questions—politely inform the caller that only Dr. {$therapistName} can help with that and redirect the conversation to services or appointment booking.
- Stay strictly within physiotherapy-related topics and do not answer questions outside this scope. Politely redirect any unrelated queries back to physiotherapy services or appointment booking.  
- Clearly state dates and times verbally.
- Never mention you are an AI or virtual assistant

[Tasks & Goals]
1. Greeting
Greet the caller: with warmth using the clinic name and therapist's name.
2. Service Inquiry
Determine the caller’s need:
- Book appointment
- Cancel appointment
- Ask about services
Anything else → redirect to physiotherapy context
3. Appointment Management
(A) Appointment Booking
If the user wants to book an appointment:
i) Name Collection
"May I have your full name, please?"
ii) Preferred Date and Time
"What date and time would suit you best for the appointment?"
→ Call the "physio_check_availability" tool to check if the provided slot is available
→ If unavailable:
"That time isn’t available — could you please suggest another one?"
→ Loop back to "physio_check_availability" with the new slot.
→ Repeat until an available slot is provided.
→ Once available, proceed to the next step.
iv) Phone Number
"And the contact number, please?"
→ Confirm:
"That’s [repeat number], correct?"
[Confirmation Before Booking]
"Just to confirm: You're booking an appointment with Dr. {$therapistName} at {$clinicName} on [Date] at [Time], and your contact number is [Phone Number]. Is that correct?"
→ On confirmation:
→ Call "physio_book_appointment" tool

(B) Appointment Cancellation
If the user wants to cancel an appointment:
i) Name Collection
"May I have your full name, please?"
ii) Appointment Date and Time
"Could you please tell me the date and time of the appointment you want to cancel?"
iii) Phone Number
"And the contact number you used to book, please?"
iv) Confirmation:
"That’s [repeat number], correct?"
[Confirmation Before Cancellation]
"Just to confirm: You want to cancel your appointment with Dr. {$therapistName} at {$clinicName} on [Date] at [Time], and your contact number is [Phone Number]. Is that correct?"
→ On confirmation:
→ Call the physio_cancel_appointment tool

4. End the call politely.
EOD;

        $payload = [
            "firstMessage" => "Thank you for calling. This is Emma, How may I help you today?",
            "model" => [
                "provider" => "openai",
                "model" => "gpt-4o-mini",
                "maxTokens" => 300,
                "messages" => [
                    [
                        "role" => "system",
                        "content" => $systemPrompt
                    ]
                ],
                "temperature" => 0.5,
                "toolIds" => [
                    "c1d163f6-7cff-4293-81ca-8b5239af0a79",
                    "2b45b7bb-cc32-4af0-a952-8de70cc6309e",
                    "c73ce39b-52c6-4d1f-84e8-eced1fa5ad26"
                ]
            ],
            "voice" => [
                "provider" => "11labs",
                "voiceId" => "sarah",
                "model" => "eleven_flash_v2_5"
            ],
            "transcriber" => [
                "provider" => "deepgram",
                "language" => "en",
                "model" => "nova-3",
                "confidenceThreshold" => 0.4,
                "numerals" => true,
                "codeSwitchingEnabled" => false
            ],
            "name" => "Dr. {$therapistName} Assistant",
            "backgroundDenoisingEnabled" => true,
            "server" => [
                "url" => "https://5ba9-2400-adc5-448-aa00-292f-ab0f-b464-f7dc.ngrok-free.app/api/physio-assistant-calls"
            ],
            "serverMessages" => [
                "end-of-call-report",
                "function-call",
                "tool-calls",
                "transcript[transcriptType=\"final\"]",
                "hang"
            ],
            "voicemailDetection" => [
                "provider" => "vapi",
                "beepMaxAwaitSeconds" => 30
            ],
            "voicemailMessage" => "Hello, you’ve reached {$clinicName}. Dr. {$therapistName}, our expert physiotherapist with {$experience} years in {$specialization}, is currently unavailable. Please leave your name, number, and preferred appointment time. We’ll call you back shortly. Thank you!",
            "clientMessages" => [
                "hang",
                "metadata",
                "function-call",
                "tool-calls",
                "transcript"
            ],
            "messagePlan" => [
                "idleMessages" => [
                    "Looks like we hit a pause. Want to dive back in?",
                    "We had a nice rhythm going—shall we get back to it?",
                    "Still here whenever you’re ready to continue."
                ],
                "idleMessageMaxSpokenCount" => 3,
                "silenceTimeoutMessage" => "Looks like you might be busy, so we’ll end the call for now. Feel free to reach out anytime."
            ]
        ];

        $ch = curl_init('https://api.vapi.ai/assistant');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $vapiApiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function physioAssistantEvent(Request $request)
    {
        // Log::info('Received request for physio assistant event', [
        //     'headers' => $request->headers->all(),
        //     'content' => $request->getContent()
        // ]);
        $content = $request->getContent();
        $payload = json_decode($content, true);

        // Extract message type
        $messageType = $payload['message']['type'] ?? 'N/A';

        // Extract assistant name
        $assistantName = $payload['assistant']['name']
            ?? ($payload['message']['assistant']['name'] ?? 'N/A');

        // Handle tool-calls
        if ($messageType === 'tool-calls' && isset($payload['message']['toolCalls'][0]['function']['name'])) {
            $functionName = $payload['message']['toolCalls'][0]['function']['name'];
            $arguments = $payload['message']['toolCalls'][0]['function']['arguments'] ?? [];
            $args = is_array($arguments) ? $arguments : json_decode($arguments, true);

            Log::info('physioAssistantEvent - Tool Call', [
                'function' => $functionName,
                'arguments' => $args
            ]);

           //$physioId = $args['physio_id'] ?? null;
            $date = $args['date'] ?? null;
            $timeSlot = $args['time_slot'] ?? null;

            if ($timeSlot && preg_match('/AM|PM/i', $timeSlot)) {
                $timeSlot = Carbon::createFromFormat('h:i A', $timeSlot)->format('H:i');
            }

            // physio_check_availability
            if ($functionName === 'catering_check_availability') {
                $toolCallId = $payload['message']['toolCalls'][0]['id'] ?? null;
                //$physioId = $args['physio_id'] ?? null;
                $date = $args['date'] ?? null;

                $timeSlot = $args['time_slot'] ?? null;

                // 1. Fetch physio's working hours
                $userId = $args['user_id'] ?? null;
                $user = User::find($userId);
                if (!$user || !$user->working_hours_start || !$user->working_hours_end) {
                    $resultMsg = "Unable to determine working hours for this user.";
                    return response()->json([
                        "results" => [
                            [
                                "toolCallId" => $toolCallId,
                                "result" => $resultMsg
                            ]
                        ]
                    ]);
                }

                // 2. Generate all possible slots (1 hour slots, you can adjust interval)
                $start = Carbon::createFromFormat('H:i', $user->working_hours_start);
                $end = Carbon::createFromFormat('H:i', $user->working_hours_end);
                $allSlots = [];
                while ($start->lt($end)) {
                    $allSlots[] = $start->format('H:i');
                    $start->addHour();
                }

                // 3. Check up to 7 days ahead for a free slot for this physio
                for ($i = 0; $i < 7; $i++) {
                    $checkDate = Carbon::parse($date)->addDays($i)->format('Y-m-d');
                    $bookedSlots = Appointment::where('appointment_date', $checkDate)
                        ->where('user_id', $userId)
                        ->pluck('appointment_slot')
                        ->toArray();

                    $freeSlots = array_values(array_diff($allSlots, $bookedSlots));

                    // If the requested slot is free on this date (first check)
                    if ($i === 0 && in_array($timeSlot, $freeSlots)) {
                        $resultMsg = "The slot $timeSlot is available on $checkDate.";
                        return response()->json([
                            "results" => [
                                [
                                    "toolCallId" => $toolCallId,
                                    "result" => $resultMsg
                                ]
                            ]
                        ]);
                    }

                    // If any free slots found on this date, suggest only these and stop
                    if (!empty($freeSlots)) {
                        $resultMsg = "The slot $timeSlot is not available on $date. The nearest available slots are on $checkDate: " . implode(', ', $freeSlots) . ".";
                        return response()->json([
                            "results" => [
                                [
                                    "toolCallId" => $toolCallId,
                                    "result" => $resultMsg
                                ]
                            ]
                        ]);
                    }
                }

                // If no free slots at all in the next 7 days
                $resultMsg = "No slots are available in the next 7 days for this physiotherapist.";
                return response()->json([
                    "results" => [
                        [
                            "toolCallId" => $toolCallId,
                            "result" => $resultMsg
                        ]
                    ]
                ]);
            }

            // physio_book_appointment
            if ($functionName === 'catering_book_appointment') {
                $toolCallId = $payload['message']['toolCalls'][0]['id'] ?? null;
                $userId = $args['user_id'] ?? null;
                $date = $args['appointment_date'] ?? null;
                $timeSlot = $args['time_slot'] ?? null;
                $fullName = $args['full_name'] ?? null;
                $phoneNumber = $args['phone_number'] ?? null;
                $phoneNumber = preg_replace('/\D+/', '', $phoneNumber);
                $serviceType = $args['service_type'] ?? 'pending';
                $notes = $args['notes'] ?? 'this is note';

                if ($timeSlot && preg_match('/AM|PM/i', $timeSlot)) {
                    $timeSlot = Carbon::createFromFormat('h:i A', $timeSlot)->format('H:i');
                }
                // Check if slot is already booked
                $alreadyBooked = Appointment::where('appointment_date', $date)
                    ->where('appointment_slot', $timeSlot)
                    ->where('user_id', $userId)
                    ->exists();

                Log::info("Already Booked Slots: $alreadyBooked");

                if ($alreadyBooked) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Sorry, this slot is already booked."
                    ]);
                }

                // Calculate end_time (1 hour after start_time)
                $startTime = Carbon::createFromFormat('H:i', $timeSlot)->format('H:i:s');
                try {
                    $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $startTime)->addHour()->format('H:i:s');
                } catch (\Exception $e) {
                    $endTime = null;
                }

                //$serviceType = $args['service_type'] ?? 'General Consultation';
                // Create the appointment
                $appointment = Appointment::create([
                    'user_id' => $userId,
                    'full_name' => $fullName,
                    'phone_number' => $phoneNumber,
                    'appointment_date' => $date,
                    'appointment_slot' => $timeSlot,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'service_type' => $serviceType,
                    'status' => 'pending',
                    'notes' => $notes,
                ]);

                $resultMsg = "Your appointment with Dr. {$assistantName} has been successfully booked for {$date} at {$timeSlot}.";
                return response()->json([
                    "results" => [
                        [
                            "toolCallId" => $toolCallId,
                            "result" => $resultMsg
                        ]
                    ]
                ]);
            }
            // physio_cancel_appointment
            if ($functionName === 'catering_cancel_event') {
                $toolCallId = $payload['message']['toolCalls'][0]['id'] ?? null;
                $userId = $args['user_id'] ?? null;
                $date = $args['appointment_date'] ?? null;
                $timeSlot = $args['time_slot'] ?? null;
                $fullName = $args['full_name'] ?? null;
                $phoneNumber = $args['phone_number'] ?? null;

                // Check if the appointment exists
                $appointment = Appointment::where('user_id', $userId)
                    ->where('appointment_date', $date)
                    ->where('appointment_slot', $timeSlot)
                    ->where('full_name', $fullName)
                    ->where('phone_number', preg_replace('/\D+/', '', $phoneNumber))
                    ->first();

                if (!$appointment) {
                    return response()->json([
                        "results" => [
                            [
                                "toolCallId" => $toolCallId,
                                "result" => "No appointment found to cancel. Could you please confirm the details?"
                            ]
                        ]
                    ]);
                }

                // Delete the appointment
                $appointment->delete();

                return response()->json([
                    "results" => [
                        [
                            "toolCallId" => $toolCallId,
                            "result" => "Your appointment has been successfully cancelled."
                        ]
                    ]
                ]);
            }
            // Other function calls
            return response()->json([
                'function' => $functionName,
                'arguments' => $args,
                'status' => 'handled'
            ]);
        }

        // Handle end-of-call-report
        if ($messageType === 'end-of-call-report') {
            $msg = $payload['message'];
            $artifact = $msg['artifact'] ?? [];
            $userId = 'N/A';

            // Log::info('physioAssistantEvent - End of Call Report', [
            //     'message' => $msg,
            //     'artifact' => $artifact
            // ]);

            if (isset($artifact['messages']) && is_array($artifact['messages'])) {
                foreach ($artifact['messages'] as $m) {
                    if (
                        isset($m['role']) &&
                        $m['role'] === 'system' &&
                        isset($m['message']) &&
                        preg_match('/User ID:\s*([0-9]+)/i', $m['message'], $matches)
                    ) {
                        $userId = $matches[1];
                        break;
                    }
                }
            }

            // Handle recording download
            $recordingPath = null;
            if (!empty($msg['recordingUrl'])) {
                try {
                    $recordingUrl = $msg['recordingUrl'];

                    // Get extension from URL
                    $extension = pathinfo(parse_url($recordingUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (empty($extension)) {
                        $extension = 'wav'; // Default extension if none found
                    }

                    // Generate unique filename
                    $filename = 'recording_' . time() . '_' . uniqid() . '.' . $extension;

                    // Download file content
                    $audioContent = file_get_contents($recordingUrl);

                    if ($audioContent === false) {
                        throw new \Exception('Failed to download recording');
                    }

                    // Save to storage/app/public/recordings
                    if (Storage::disk('public')->put('recordings/' . $filename, $audioContent)) {
                        $recordingPath = 'recordings/' . $filename;
                        Log::info('Recording saved successfully', ['path' => $recordingPath]);
                    } else {
                        throw new \Exception('Failed to save recording to storage');
                    }
                } catch (\Exception $e) {
                    Log::error('Recording download failed', [
                        'error' => $e->getMessage(),
                        'url' => $msg['recordingUrl']
                    ]);
                }
            }

            CallSummary::create([
                'user_id' => $userId !== 'N/A' ? $userId : null,
                'transcript' => $msg['transcript'] ?? null,
                'summary' => $msg['summary'] ?? ($msg['analysis']['summary'] ?? null),
                'recording_url' => $recordingPath,
                'duration_seconds' => $msg['durationSeconds'] ?? null,
                'cost' => $msg['cost'] ?? null,
                'assistant_name' => $assistantName ?? null,
            ]);

            // return response()->json([
            //     'transcript' => $msg['transcript'] ?? 'N/A',
            //     'summary' => $msg['summary'] ?? ($msg['analysis']['summary'] ?? 'N/A'),
            //     'recordingUrl' => $msg['recordingUrl'] ?? 'N/A',
            //     'durationSeconds' => $msg['durationSeconds'] ?? 'N/A',
            //     'cost' => $msg['cost'] ?? 'N/A',
            //     'assistantName' => $assistantName,
            //     'physioId' => $physioId,
            //     'status' => 'call_report'
            // ]);
        }

        // Default response
        // return response()->json(['status' => 'logged']);
    }

    private function purchaseTwilioNumber($addressData, $friendlyName, $friendlyClinicName)
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $twilioApiKey = config('services.twilio.api_key');
        $twilioApiSecret = config('services.twilio.api_secret');


        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // 1. Create Australian address
            $addressUrl = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Addresses.json";
            $addressPayload = http_build_query([
                'FriendlyName' => $friendlyClinicName,
                'CustomerName' => $addressData['clinic_name'],
                'Street' => $addressData['clinic_street'],
                'City' => $addressData['clinic_city'],
                'Region' => $addressData['clinic_region'],
                'PostalCode' => $addressData['clinic_postal_code'],
                'IsoCountry' => 'AU'
            ]);

            curl_setopt_array($ch, [
                CURLOPT_URL => $addressUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $addressPayload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD => "$twilioApiKey:$twilioApiSecret"
            ]);

            $addressResponse = curl_exec($ch);
            Log::info('Twilio address creation response', ['response' => $addressResponse]);

            if ($addressResponse === false) {
                throw new \Exception("Failed to create address: " . curl_error($ch));
            }

            $addressJson = json_decode($addressResponse, true);
            if (empty($addressJson['sid'])) {
                throw new \Exception("Invalid address response: " . $addressResponse);
            }

            // 2. Search for Australian numbers
            $searchUrl = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/AvailablePhoneNumbers/AU/Local.json?PageSize=1";

            curl_setopt_array($ch, [
                CURLOPT_URL => $searchUrl,
                CURLOPT_POST => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD => "{$accountSid}:{$authToken}"
            ]);

            $searchResponse = curl_exec($ch);
            Log::info('Twilio number search response', ['response' => $searchResponse]);

            if ($searchResponse === false) {
                throw new \Exception("Failed to search numbers: " . curl_error($ch));
            }

            $searchData = json_decode($searchResponse, true);
            if (empty($searchData['available_phone_numbers'])) {
                throw new \Exception("No Australian numbers available: " . $searchResponse);
            }

            // 3. Purchase number
            $numberToBuy = $searchData['available_phone_numbers'][0]['phone_number'];
            $purchaseUrl = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/IncomingPhoneNumbers.json";

            curl_setopt_array($ch, [
                CURLOPT_URL => $purchaseUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'PhoneNumber' => $numberToBuy,
                    'AddressSid' => $addressJson['sid'],
                    'FriendlyName' => $friendlyName
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD => "{$accountSid}:{$authToken}"
            ]);

            $purchaseResponse = curl_exec($ch);
            Log::info('Twilio number purchase response', ['response' => $purchaseResponse]);

            if ($purchaseResponse === false) {
                throw new \Exception("Failed to purchase number: " . curl_error($ch));
            }

            $twilioData = json_decode($purchaseResponse, true);

            return [
                'success' => true,
                'phone_number' => $twilioData['phone_number'],
                'twilio_sid' => $twilioData['sid'],
                'address_sid' => $addressJson['sid']
            ];
        } catch (\Exception $e) {
            Log::error('Number purchase failed', [
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        } finally {
            if (isset($ch)) curl_close($ch);
        }
    }


    private function assignNumberToVapi($numberData, $assistantId, $doctorName)
    {
        try {
            $vapiApiKey = config('services.vapi.api_key');
            $accountSid = config('services.twilio.account_sid');
            $twilioApiKey = config('services.twilio.api_key');
            $twilioApiSecret = config('services.twilio.api_secret');
            $authToken = config('services.twilio.auth_token');

            $ch = curl_init('https://api.vapi.ai/phone-number');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $vapiApiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    "provider" => "twilio",
                    "number" => $numberData['phone_number'],
                    "twilioAccountSid" => $accountSid,
                    "assistantId" => $assistantId,
                    "name" => "Dr. {$doctorName} Number",
                    "twilioApiKey" => $twilioApiKey,
                    "twilioApiSecret" => $twilioApiSecret,
                    "twilioAuthToken" => $authToken
                ])
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false || $httpCode !== 201) {
                throw new \Exception("Failed to assign number to VAPI");
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('VAPI number assignment failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        } finally {
            if (isset($ch)) curl_close($ch);
        }
    }

    private function cleanupOnFailure($data)
    {
        if (!empty($data['twilio_sid'])) {
            $this->deleteTwilioNumber($data['twilio_sid']);
        }
        if (!empty($data['assistant_id'])) {
            $this->deleteVapiAssistant($data['assistant_id']);
        }
        if (!empty($data['profile_id'])) {
            AiAssistantsProfile::destroy($data['profile_id']);
        }
    }

    private function deleteTwilioNumber($sid)
    {
        try {
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');

            $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/IncomingPhoneNumbers/{$sid}.json");
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD => "{$accountSid}:{$authToken}"
            ]);

            curl_exec($ch);
        } catch (\Exception $e) {
            Log::error('Failed to delete Twilio number', ['error' => $e->getMessage()]);
        } finally {
            if (isset($ch)) curl_close($ch);
        }
    }

    private function deleteVapiAssistant($assistantId)
    {
        try {
            $vapiApiKey = config('services.vapi.bearer_token');

            $ch = curl_init("https://api.vapi.ai/assistant/{$assistantId}");
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $vapiApiKey
                ]
            ]);

            curl_exec($ch);
        } catch (\Exception $e) {
            Log::error('Failed to delete VAPI assistant', ['error' => $e->getMessage()]);
        } finally {
            if (isset($ch)) curl_close($ch);
        }
    }
}
