<?php

namespace App\Observers;

use App\Models\Feedback;
use Illuminate\Support\Facades\DB;

class FeedbackObserver
{

    public function saved(Feedback $feedback)
    {
        $this->updateProfessorCourseRating($feedback);
    }


    public function deleted(Feedback $feedback)
    {
        $this->updateProfessorCourseRating($feedback);
    }


    protected function updateProfessorCourseRating(Feedback $feedback)
    {
        $avgRating = Feedback::where('course_id', $feedback->course_id)
            ->where('professor_id', $feedback->professor_id)
            ->avg('rating');


        DB::table('course_professor')
            ->where('course_id', $feedback->course_id)
            ->where('professor_id', $feedback->professor_id)
            ->update(['average_rating' => $avgRating]);
    }
}