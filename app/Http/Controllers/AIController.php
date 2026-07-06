<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Cache;
class AIController extends Controller
{
    public function analyzeProfessor(
        string $username,
        GeminiService $gemini
    ): JsonResponse {

        $professor = User::where('username', $username)
            ->where('role', 'professor')
            ->with('feedbacks')
            ->first();

        if (!$professor) {

            return response()->json([
                'success' => false,
                'message' => 'Professor not found'
            ],404);

        }

        if ($professor->feedbacks->isEmpty()) {

            return response()->json([
                'success' => false,
                'message' => 'No feedback found'
            ]);

        }

        $feedbackText = $professor->feedbacks
            ->filter(fn ($feedback) => !empty($feedback->comment))
            ->map(function ($feedback) {
                return
                    "Rating: {$feedback->rating}/5\n" .
                    "Comment: {$feedback->comment}";
            })
            ->implode("\n\n");


        $averageRating = round($professor->feedbacks->avg('rating'), 2);

        $totalFeedbacks = $professor->feedbacks->count();

        $prompt = <<<PROMPT

You are an experienced university education analyst.

Your task is to analyze anonymous student feedback about a professor.

Rules:

- Be objective.
- Ignore duplicated comments.
- Ignore emotional language.
- Focus on repeated patterns.
- Never invent facts.
- Base every conclusion only on the provided feedback.
- If there is not enough information, say so.

Return ONLY valid JSON.

Schema:

{
  "summary": "",
  "strengths": [],
  "weaknesses": [],
  "recommended_for": [],
  "overall_score": 0,
  "confidence": 0
}

Professor Information

Average Rating:
{$averageRating}/5

Number of Feedbacks:
{$totalFeedbacks}

Feedback:

{$feedbackText}

PROMPT;

$result = Cache::remember(
    "professor_ai_analysis_{$professor->id}",
    now()->addHours(24),
    function () use ($gemini, $prompt) {
        return $gemini->generate($prompt);
    }
);

if (!$result['success']) {
    return response()->json([
        'success' => false,
        'message' => 'AI analysis is currently unavailable. Please try again later.'
    ], 503);
}

return response()->json($result);

    }
}
