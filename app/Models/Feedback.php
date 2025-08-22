<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{

    protected $table = 'feedbacks';
    protected $fillable = [
        'student_id', 
        'professor_id', 
        'course_id', 
        'rating', 
        'comment',
        'created_at',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id')
            ->where('role', 'student')
            ->select('id', 'full_name', 'avatar','username','major','role');
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id')
            ->where('role', 'professor')
            ->select('id', 'full_name','username', 'department', 'avatar');
    }

    public function course()
    {
        return $this->belongsTo(Course::class)
            ->select('id', 'title', 'slug', 'course_code');
    }
}