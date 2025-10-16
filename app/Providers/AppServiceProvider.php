<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Models\Settings;
use App\Http\View\Composers\LanguageComposer;

/**
 * Application Service Provider
 * 
 * This service provider is responsible for bootstrapping application services
 * including database schema configuration, view sharing, and view composers.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register application services here
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set default string length for MySQL compatibility
        Schema::defaultStringLength(191);
        
        // Share settings with all views to avoid Undefined variable errors in layouts
        $this->shareSettingsWithViews();
        
        // Register Language Composer for admin and frontend views
        $this->registerViewComposers();
    }

    /**
     * Share settings with all views
     * 
     * @return void
     */
    private function shareSettingsWithViews()
    {
        try {
            $settings = Settings::first();
            View::share('settings', $settings);
        } catch (\Exception $e) {
            // If DB isn't ready during certain artisan commands, silently ignore
            \Log::warning('Settings could not be loaded: ' . $e->getMessage());
        }
    }

    /**
     * Register view composers
     * 
     * @return void
     */
    private function registerViewComposers()
    {
        View::composer([
            'backend.layouts.*',
            'backend.*',
            'frontend.layouts.*',
            'frontend.*'
        ], LanguageComposer::class);
    }
}
