<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    protected static function boot()
    {
        parent::boot();

        // ایجاد اسلاگ هنگام ذخیره‌سازی یا بروزرسانی
        static::creating(function ($course) {
            $course->slug = Str::slug($course->title);
        });

        static::updating(function ($course) {
            $course->slug = Str::slug($course->title);
        });
    }

    protected $table = 'courses';

    protected $fillable = ['title', 'description', 'slug', 'credits', 'course_code'];

    // رابطه با استاد
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
                    //->where('role', 'professor'); // فقط کاربران با نقش استاد
    }
}