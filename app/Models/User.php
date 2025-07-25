<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\MustVerifyEmail; // اضافه کردن این کلاس برای تأیید ایمیل
 /**
 * @property string $role
 * @property float $average_rating
 */

class User extends Authenticatable implements MustVerifyEmail // پیاده‌سازی MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'role',
        'student_number',
        'faculty_number',
        'major',
        'department',
        'average_rating',
        'verification_token',
        'token_expires_at',
        'is_approved',
        'created_by',
        'updated_by',
        'is_board_member',
        'teaching_experience',
        'comments_count',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_approved' => 'boolean',
        'token_expires_at' => 'datetime',
        'average_rating' => 'float',
    ];

    /**
     * روابط
     */

    // رابطه دانشجویان با دوره‌ها (Many-to-Many)
    //public function coursesForStudents()
    ////{
        ////return $this->belongsToMany(Course::class, 'course_student', 'student_id', 'course_id')
    ///}

    // رابطه استاد با دوره‌ها (One-to-Many)
    public function courses()
    {
        return $this->hasMany(Course::class, 'professor_id');
    }

    // رابطه استاد با فیدبک‌ها
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'professor_id')
                    ->where('role', 'professor'); // فقط فیدبک‌های مربوط به استاد
    }

    /**
     * سایر متدها
     */
    // تولید توکن تایید برای دانشجو
    public static function generateVerificationToken()
    {
        return Str::random(60);
    }

    // محاسبه میانگین رتبه برای استاد
    public function calculateAverageRating()
    {
        if ($this-> role === 'professor') {
            $this->average_rating = $this->feedbacks()->avg('rating');
            $this->save();
        }
        return $this->average_rating;
    }

    /**
     * اسکوپ‌ها
     */

    // دانشجویان تایید شده
    public function scopeApprovedStudents($query)
    {
        return $query->where('role', 'student')->where('is_approved', true);
    }

    // مرتب‌سازی استادها بر اساس رتبه
    public function scopeSortedProfessorsByRating($query)
    {
        return $query->where('role', 'professor')->orderBy('average_rating', 'desc');
    }
}