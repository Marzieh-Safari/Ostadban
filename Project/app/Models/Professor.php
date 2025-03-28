<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    protected $fillable = ['name', 'department', 'average_rating','password','faculty_number','is_approved'];
    


    // app/Models/Professor.php

    public static function getMostSearchedLastMonth($limit = 10)
    {
        return self::orderBy('search_count', 'desc')
                    ->where('updated_at', '>=', now()->subDays(30)) // فقط رکوردهای ۳۰ روز گذشته
                    ->take($limit)
                    ->get();
    }
    public function courses() {
        return $this->hasMany(Course::class);
    }

    public function feedbacks() {
        return $this->hasMany(Feedback::class);
    }
    public static function getSortedByRating()
    {
        // مرتب‌سازی اساتید بر اساس میانگین امتیاز از بهترین به بدترین
        return self::orderBy('average_rating', 'desc')->get();
    }
}