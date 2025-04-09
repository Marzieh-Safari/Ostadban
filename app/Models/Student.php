<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'email', 
        'phone', 
        'is_approved',
        'password',
        'student_number',
        'major',
        'verification_token',
        'token_expires_at',
        'email_verified_at',
        'type'
    ];

    protected $hidden = [
        'password',
        'verification_token',
        'remember_token'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'token_expires_at' => 'datetime',
        'email_verified_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            $student->type = 'student';
            $student->verification_token = Str::random(60);
            $student->token_expires_at = now()->addHours(24);
        });
    }

    public static function generateVerificationToken()
    {
        return Str::random(60);
    }

    // (رابطه با کورس‌ها)
    //public function courses()
    //{
        //return $this->belongsToMany(Course::class, 'course_student', 'student_id', 'course_id');
    //}
}