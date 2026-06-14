<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Http;

class InvoiceAiController extends Controller
{
    public function extract(Request $request)
    {
        $request->validate([
            'invoice_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $settings = CompanySetting::first();
        if (!$settings || empty($settings->gemini_api_key)) {
            return response()->json(['error' => 'Gemini API Key is not configured in Company Settings.'], 400);
        }

        $imageFile = $request->file('invoice_file');
        $base64Image = base64_encode(file_get_contents($imageFile->path()));
        $mimeType = $imageFile->getClientMimeType();

        $prompt = "You are an AI trained to extract data from invoices. Analyze this invoice document (image or PDF) and extract the following information in strict JSON format:
        {
            \"customer_name\": \"Extracted customer or company name being billed\",
            \"invoice_date\": \"YYYY-MM-DD\",
            \"due_date\": \"YYYY-MM-DD\",
            \"items\": [
                {
                    \"description\": \"Item description\",
                    \"quantity\": 1,
                    \"price\": 100.50
                }
            ]
        }
        Only return the JSON. Do not include markdown formatting or any other text.";

        try {
            $response = Http::post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $settings->gemini_api_key, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonString = $data['candidates'][0]['content']['parts'][0]['text'];
                    // Strip markdown if it exists
                    $jsonString = str_replace(['```json', '```'], '', $jsonString);
                    $jsonString = trim($jsonString);
                    
                    $extractedData = json_decode($jsonString, true);
                    return response()->json($extractedData);
                }
                
                return response()->json(['error' => 'Could not parse response from AI.'], 500);
            }

            return response()->json(['error' => 'API request failed: ' . $response->body()], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Exception occurred: ' . $e->getMessage()], 500);
        }
    }
}
