<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = '';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        Route::middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
