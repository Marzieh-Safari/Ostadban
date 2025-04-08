<?php

// app/Models/Student.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
// app/Models/Student.php

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'is_approved','password','student_number','major','verification_token','token_expires_at','email_verified_at']; // فیلدهای قابل پر شدن
    // app/Models/Student.php
    protected $hidden = ['password','verification_token','remember_token']; // مخفی کردن پسورد در پاسخ‌ها
    protected $casts = ['is_approved' => 'boolean','token_expires_at' => 'datetime','email_verified_at' => 'datetime'];
    public static function generateVerificationToken()
    {
        return Str::random(60);
    }
}