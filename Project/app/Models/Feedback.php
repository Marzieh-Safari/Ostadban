<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['student_id', 'professor_id', 'rating', 'comment'];

    protected $table = 'feedbacks';
    public function student() {
        return $this->belongsTo(student::class);
    }

    public function professor() {
        return $this->belongsTo(Professor::class);
    }
    public function course() {
        return $this->belongsTo(Course::class);
    }
}