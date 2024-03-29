<?php

namespace {{PROJECT_NAMESPACE}}\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api/{{PROJECT_FILE_NAME}}')
                ->group(__DIR__ . '/../../routes/api.php');
        });
    }
}
