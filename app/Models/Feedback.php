<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['student_number', 'faculty_number', 'rating', 'comment'];

    protected $table = 'feedbacks';
    public function student()
{
    return $this->belongsTo(User::class, 'student_number', 'student_')
                ->where('type', 'student'); // فقط کاربران با نقش دانشجو
}
public function professor()
{
    return $this->belongsTo(User::class, 'faculty_number', 'faculty_number')
                ->where('type', 'professor'); // فقط کاربران با نقش استاد
}
    public function course() {
        return $this->belongsTo(Course::class);
    }
}