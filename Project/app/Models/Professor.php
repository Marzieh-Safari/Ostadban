<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    protected $fillable = ['name', 'department', 'average_rating','password','faculty_number','is_approved'];

    public function courses() {
        return $this->hasMany(Course::class);
    }

    public function feedbacks() {
        return $this->hasMany(Feedback::class);
    }
}