<?php

namespace App\Providers;
use App\Menus\MainMenu;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        view()->composer('*', function ($view) {
            $view->with('mainMenu', MainMenu::build());
        });
    }
}
