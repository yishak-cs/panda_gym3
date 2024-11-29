<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    $ReceptionistMenuJson = file_get_contents(base_path('resources/menu/ReceptionistMenu.json'));
    $MenuData = json_decode($ReceptionistMenuJson);

    // Share all menuData to all the views
    $this->app->make('view')->share('menuData', [$MenuData]);
  }
}
