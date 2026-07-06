<?php

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ProfessorAnalysisService
{
    public function __construct(
        private GeminiService $gemini
    ) {
    }

    public function analyze(User $professor): array
    {
        return Cache::remember(
            "professor_ai_analysis_{$professor->id}",
            now()->addHours(24),
            function () use ($professor) {

                // ساخت متن فیدبک‌ها
                $feedbackText = $this->buildFeedback($professor);

                // محاسبه میانگین امتیاز
                $averageRating = round(
                    $professor->feedbacks->avg('rating'),
                    2
                );

                // تعداد کل نظرات
                $totalFeedbacks = $professor->feedbacks->count();

                // ساخت Prompt
                $prompt = <<<PROMPT
You are an expert educational analyst.

You are analyzing anonymous university student feedback about a professor.

Instructions:

- Be objective.
- Never invent information.
- Ignore duplicated comments.
- Ignore emotional language.
- Focus on repeated patterns.
- Base every conclusion ONLY on the provided feedback.
- If information is insufficient, explicitly mention it.

Return ONLY valid JSON.

Schema:

{
"summary":"",
"strengths":[],
"weaknesses":[],
"recommended_for":[],
"overall_score":0,
"confidence":0
}

Professor Statistics

Average Rating:
{$averageRating}/5

Total Feedbacks:
{$totalFeedbacks}

Feedback:

{$feedbackText}
PROMPT;

                return $this->gemini->generate($prompt);
            }
        );
    }

    /**
     * ساخت متن فیدبک‌ها برای ارسال به Gemini
     */
    private function buildFeedback(User $professor): string
    {
        return $professor->feedbacks
            ->filter(fn ($feedback) => filled($feedback->comment))
            ->map(function ($feedback) {

                return
                    "Rating: {$feedback->rating}/5\n" .
                    "Comment: {$feedback->comment}";

            })
            ->implode("\n\n");
    }
}
