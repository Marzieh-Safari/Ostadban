<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            $course->slug = Str::slug($course->name);
        });

        static::updating(function ($course) {
            $course->slug = Str::slug($course->name);
        });
    }
    protected $fillable = ['title', 'description', 'faculty_number'];

    public function professor()
    {
    return $this->belongsTo(Professor::class, 'faculty_number', 'id');
    }

}