<?php

// app/Models/AdminSystem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSystem extends Model
{
    use HasFactory;

    protected $fillable = ['username', 'email', 'password']; // فیلدهای قابل پر شدن

    // هش کردن رمز عبور هنگام ذخیره
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}