<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VapiCallController extends Controller
{
    public function initiateCall(Request $request)
    {
        // Validate request
        $request->validate([
            'phone_number' => 'required|string',
            'name' => 'nullable|string',
        ]);

        // Access environment variables
        $bearerToken = config('services.vapi.api_key');
        $assistantId = config('services.vapi.assistant_id');
        $phoneNumberId = config('services.vapi.phone_number_id');

        if (!$bearerToken || !$assistantId || !$phoneNumberId) {
            return response()->json([
                'status' => 'error',
                'message' => 'API configuration is missing.'
            ], 422);
        }

        // Get data from request
        $phoneNumber = $request->phone_number;
        $name = $request->name ?? 'Patient';

        // Ensure phone number format
        $phoneNumber = "+" . ltrim($phoneNumber, '+');

        // Prepare the API request
        $data = [
            "assistantId" => $assistantId,
            "customer" => [
                "number" => $phoneNumber,
                "name" => $name,
            ],
            "phoneNumberId" => $phoneNumberId
        ];

        // Make the API call
        $response = $this->makeApiCall($bearerToken, $data);

        return $response;
    }

    private function makeApiCall($bearerToken, $data)
    {
        $headers = [
            "Authorization: Bearer $bearerToken",
            "Content-Type: application/json"
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.vapi.ai/call",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // Decode the API response
        $responseData = json_decode($response, true);

        // Log response for debugging
        Log::info('Vapi API response', ['response' => $responseData]);

        // Handle response
        if (isset($responseData['id'])) {
            return response()->json([
                'status' => 'success',
                'message' => 'Call initiated successfully!',
                'data' => $responseData
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate call.',
                'details' => $responseData
            ], 422);
        }
    }
}
