<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role', 'verified'];

    public function feedbacks() {
        return $this->hasMany(Feedback::class);
    }

}
