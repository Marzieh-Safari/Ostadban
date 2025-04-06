<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    protected $fillable = ['full_name', 'department', 'average_rating','username','faculty_number'];
    protected $table = 'professors';


    // app/Models/Professor.php

    public static function getMostSearchedLastMonth($limit = 10)
    {
        return self::orderBy('search_count', 'desc')
                    ->where('updated_at', '>=', now()->subDays(30)) // فقط رکوردهای ۳۰ روز گذشته
                    ->take($limit)
                    ->get();
    }
    public function courses() {
        return $this->hasMany(Course::class, 'faculty_number', 'faculty_number');
    }

    public function feedback() {
        return $this->hasMany(Feedback::class);
    }
    public static function getSortedByRating()
    {
    return self::with('courses') // بارگذاری همراه با درس‌ها
        ->orderBy('average_rating', 'desc')
        ->get();
    }
    //public function getAverageRatingAttribute() {
        //return $this->ratings()->avg('rating');
    //}
}