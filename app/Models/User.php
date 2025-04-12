<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * فیلدهایی که قابل پر شدن هستند
     */
    protected $fillable = [
        'full_name',        // نام کامل (برای استاد و ادمین)
        'name',             // نام (برای دانشجو)
        'username',         // نام کاربری
        'email',            // ایمیل
        'password',         // رمز عبور
        'email_verified_at', // تایید ایمیل
        'type',             // نوع کاربر (student, professor, admin)
        'faculty_number',   // شماره دانشکده (برای استاد)
        'student_number',   // شماره دانشجویی (برای دانشجو)
        'major',            // رشته (برای دانشجو)
        'department',       // دپارتمان (برای استاد)
        'average_rating',   // میانگین امتیاز (برای استاد)
        'permissions',      // سطح دسترسی (برای ادمین)
        'last_login_at',    // زمان آخرین ورود (برای ادمین)
        'status',           // وضعیت فعال یا غیرفعال بودن (برای ادمین)
        'verification_token', // توکن تایید (برای دانشجو)
        'token_expires_at', // زمان انقضای توکن تایید (برای دانشجو)
        'is_approved',      // تایید شدن دانشجو
        'search_count',     // تعداد جستجوهای استاد
    ];

    /**
     * فیلدهایی که باید مخفی شوند
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token', // فقط برای دانشجو
    ];

    /**
     * تبدیل‌های نوع (Casting)
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_approved' => 'boolean',
        'token_expires_at' => 'datetime',
        'average_rating' => 'float',
        'permissions' => 'json',
        'last_login_at' => 'datetime',
    ];

    /**
     * Booting model برای تعیین پیش‌فرض‌ها
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // تنظیم نوع کاربر بر اساس مقادیر موجود
            if (!$user->type) {
                $user->type = 'student'; // پیش‌فرض نوع دانشجو
            }

            // تنظیم مقادیر خاص برای دانشجو
            if ($user->type === 'student') {
                $user->verification_token = Str::random(60);
                $user->token_expires_at = now()->addHours(24);
            }

            // تنظیم مقادیر خاص برای استاد
            if ($user->type === 'professor') {
                $user->type = 'professor';
            }

            // تنظیم مقادیر خاص برای ادمین
            if ($user->type === 'admin') {
                $user->type = 'admin';
            }
        });
    }

    /**
     * روابط عمومی
     */

    // رابطه دانشجو با دوره‌ها
    public function coursesForStudents()
    {
        return $this->belongsToMany(Course::class, 'course_student', 'student_id', 'course_id')
            ->where('type', 'student');
    }

    // رابطه استاد با دوره‌ها
    public function coursesForProfessor()
    {
        return $this->hasMany(Course::class, 'faculty_number', 'faculty_number')
            ->where('type', 'professor');
    }

    // رابطه استاد با فیدبک‌ها
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'professor_id')
            ->where('type', 'professor');
    }

    /**
     * متدهای اختصاصی برای کاربران خاص
     */

    // محاسبه میانگین رتبه برای استاد
    public function calculateAverageRating()
    {
        if ($this->type === 'professor') {
            $this->average_rating = $this->feedback()->avg('rating');
            $this->save();
        }
        return $this->average_rating;
    }

    // بررسی سطح دسترسی برای ادمین
    public function hasPermission($permission)
    {
        if ($this->type === 'admin') {
            return in_array($permission, $this->permissions ?? []);
        }
        return false;
    }

    // ثبت آخرین زمان ورود برای ادمین
    public function recordLastLogin()
    {
        if ($this->type === 'admin') {
            $this->last_login_at = now();
            $this->save();
        }
    }

    // فعال کردن ادمین
    public function activate()
    {
        if ($this->type === 'admin') {
            $this->status = 'active';
            $this->save();
        }
    }

    // غیرفعال کردن ادمین
    public function deactivate()
    {
        if ($this->type === 'admin') {
            $this->status = 'inactive';
            $this->save();
        }
    }

    // تولید توکن تایید برای دانشجو
    public static function generateVerificationToken()
    {
        return Str::random(60);
    }

    /**
     * اسکوپ‌ها
     */

    // فقط ادمین‌های فعال
    public function scopeActiveAdmins($query)
    {
        return $query->where('type', 'admin')->where('status', 'active');
    }

    // دانشجویان تایید شده
    public function scopeApprovedStudents($query)
    {
        return $query->where('type', 'student')->where('is_approved', true);
    }

    // مرتب‌سازی استادها بر اساس رتبه
    public function scopeSortedProfessorsByRating($query)
    {
        return $query->where('type', 'professor')->orderBy('average_rating', 'desc');
    }

    // استادهای پرجستجو در ماه گذشته
    public function scopeMostSearchedProfessorsLastMonth($query, $limit = 10)
    {
        return $query->where('type', 'professor')
            ->orderBy('search_count', 'desc')
            ->where('updated_at', '>=', now()->subDays(30))
            ->take($limit);
    }
}