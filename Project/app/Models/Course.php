<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($courses) {
            $courses->slug = Str::slug($courses->title);
        });

        static::updating(function ($courses) {
            $courses->slug = Str::slug($courses->title);
        });
    }
    protected $table = 'courses';
    protected $fillable = ['title', 'slug', 'faculty_number'];

    public function professor()
    {
    return $this->belongsTo(Professor::class, 'faculty_number', 'id');
    }

}