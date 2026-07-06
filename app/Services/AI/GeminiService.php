<?php

namespace App\Services\AI;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $url;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini_key');

        $this->url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}";
    }

    public function generate(string $prompt): array
    {
        try {

            $response = Http::timeout(60)
                ->acceptJson()
                ->post($this->url, [

                    "contents" => [
                        [
                            "parts" => [
                                [
                                    "text" => $prompt
                                ]
                            ]
                        ]
                    ]

                ]);

            if (!$response->successful()) {

                return [
                    'success' => false,
                    'message' => 'Gemini request failed'
                ];

            }

            $text = data_get(
                $response->json(),
                'candidates.0.content.parts.0.text'
            );

            $decoded = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {

                return [
                    'success' => true,
                    'data' => $decoded
                ];

            }

            return [
                'success' => true,
                'data' => [
                    'raw' => $text
                ]
            ];

        } catch (Exception $e) {

            Log::error($e);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

        }
    }
}
