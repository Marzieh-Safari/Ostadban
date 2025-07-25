<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['student_id', 'professor_id', 'rating', 'comment'];

    protected $table = 'feedbacks';

    // رابطه با دانشجو
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id')
                    ->where('role', 'student'); // فقط کاربران با نقش دانشجو
    }

    // رابطه با استاد
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id')
                    ->where('role', 'professor'); // فقط کاربران با نقش استاد
    }

    // رابطه با دوره
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id'); // فرض بر این است که فیلد course_id وجود دارد
    }
}