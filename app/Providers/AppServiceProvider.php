<?php


namespace App\Providers;


use App\Models\Feedback;
use App\Observers\FeedbackObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind('path.to.interface', 'path.to.implementation');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
{
    Feedback::observe(FeedbackObserver::class);
}
}