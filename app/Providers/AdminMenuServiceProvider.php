<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AdminMenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/Admin.json'));
        $verticalMenuData = json_decode($verticalMenuJson);

        // Share all menuData to all the views
        $this->app->make('view')->share('adminData', [$verticalMenuData]);
    }
}
