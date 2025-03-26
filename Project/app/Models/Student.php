<?php

// app/Models/Student.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'major','password','student_number']; // فیلدهای قابل پر شدن
}