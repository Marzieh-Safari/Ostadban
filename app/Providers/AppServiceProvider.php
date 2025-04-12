<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ثبت سرویس‌های برنامه در اینجا
        // مثال:
        // $this->app->bind('path.to.interface', 'path.to.implementation');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // راه‌اندازی سرویس‌ها و کامپوننت‌ها
        // مثالهای رایج:
        
        // 1. تنظیم Paginator پیش‌فرض
        // Paginator::useBootstrapFive();
        // یا
        // Paginator::defaultView('vendor.pagination.bootstrap-5');
        
        // 2. تنظیم Schema پیش‌فرض برای دیتابیس
        // Schema::defaultStringLength(191);
        
        // 3. رجیستر کردن کامپوننت‌های Blade
        // Blade::component('package-card', PackageCardComponent::class);
        
        // 4. تنظیمات مربوط به زمان‌بندی کارها
        // Model::preventLazyLoading(! $this->app->isProduction());
    }
}