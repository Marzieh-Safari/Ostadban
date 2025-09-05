<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = ['title', 'description', 'slug', 'credits', 'course_code','comments_count'];


    public function professors()
    {
        return $this->belongsToMany(User::class, 'course_professor', 'course_id', 'professor_id')
            ->withPivot('average_rating'); 
    }


    public function feedbacks()
    {
    return $this->hasMany(Feedback::class, 'course_id');
    }
}