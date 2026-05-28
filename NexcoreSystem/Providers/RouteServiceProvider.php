<?php

namespace Modules\NexcoreSystem\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\NexcoreSystem\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware(['web', 'auth'])
            ->namespace($this->moduleNamespace)
            ->prefix('nexcore/system')
            ->name('nexcore.system.')
            ->group(module_path('NexcoreSystem', '/Routes/web.php'));
    }
}
