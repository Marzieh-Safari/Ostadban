<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = ['title', 'description', 'slug', 'credits', 'course_code','comments_count'];

    // رابطه با استاد
    public function professors()
    {
        return $this->belongsToMany(User::class, 'course_professor', 'course_id', 'professor_id');
                //->where('role', 'professor');
    }
}