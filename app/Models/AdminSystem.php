<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminSystem extends Authenticatable
{
    use Notifiable;

    // فیلدهایی که قابل پر شدن هستند
    protected $fillable = [
        'username',
        'full_name', // نام کامل
        'type', // باید به صورت پیش‌فرض 'admin' باشد
        'email',
        'password',
        'email_verified_at',
        'permissions', // سطح دسترسی‌های ادمین
        'last_login_at', // زمان آخرین ورود ادمین
        'status', // فعال یا غیرفعال بودن
    ];

    // فیلدهایی که باید مخفی شوند
    protected $hidden = [
        'password',
        'remember_token'
    ];

    // تبدیل‌های نوع (Casting)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'json', // اگر سطح دسترسی‌ها به صورت JSON ذخیره می‌شود
        'last_login_at' => 'datetime',
    ];

    // Booting model برای تعیین پیش‌فرض‌ها
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            $admin->type = 'admin'; // تعیین نوع ادمین به صورت پیش‌فرض
        });
    }
    // اسکوپ فقط ادمین‌های فعال
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // اسکوپ مرتب‌سازی بر اساس زمان آخرین ورود
    public function scopeOrderedByLastLogin($query)
    {
        return $query->orderBy('last_login_at', 'desc');
    }

    /**
     * متدهای کمکی (Utility Methods)
     */

    // ثبت آخرین زمان ورود
    public function recordLastLogin()
    {
        $this->last_login_at = now();
        $this->save();
    }

    // بررسی یک دسترسی خاص
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }

    // غیرفعال کردن ادمین
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();
    }

    // فعال کردن ادمین
    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }
}