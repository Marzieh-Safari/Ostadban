<?php

// app/Models/Student.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// app/Models/Student.php

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'is_approved','password','student_number','major']; // فیلدهای قابل پر شدن
    // app/Models/Student.php
    protected $hidden = ['password']; // مخفی کردن پسورد در پاسخ‌ها
    protected $casts = ['is_approved' => 'boolean'];
}