<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Professor extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'full_name', // حفظ همان فیلد قبلی
        'department',
        'average_rating',
        'username',
        'faculty_number',
        'type',
        'email', 
        'password',
        'email_verified_at',
        'search_count' // اضافه شده در صورت نیاز
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'average_rating' => 'float'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($professor) {
            $professor->type = 'professor';
        });
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'faculty_number', 'faculty_number');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'professor_id');
    }

    public static function getMostSearchedLastMonth($limit = 10)
    {
        return self::where('type', 'professor')
            ->orderBy('search_count', 'desc')
            ->where('updated_at', '>=', now()->subDays(30))
            ->take($limit)
            ->get();
    }

    public static function getSortedByRating()
    {
        return self::where('type', 'professor')
            ->with('courses')
            ->orderBy('average_rating', 'desc')
            ->get();
    }

    // اکسسور برای تطابق با انتظارات لاراول (اختیاری)
    public function getNameAttribute()
    {
        return $this->attributes['full_name'];
    }

    // برای محاسبه میانگین رتبه
    public function calculateAverageRating()
    {
        $this->average_rating = $this->feedback()->avg('rating');
        $this->save();
        return $this->average_rating;
    }
}