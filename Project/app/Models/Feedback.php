<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['user_id', 'professor_id', 'rating', 'comment'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function professor() {
        return $this->belongsTo(Professor::class);
    }
}