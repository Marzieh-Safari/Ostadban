<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title', 'description', 'faculty_number'];

    public function professor()
    {
    return $this->belongsTo(Professor::class, 'faculty_number', 'id');
    }

}