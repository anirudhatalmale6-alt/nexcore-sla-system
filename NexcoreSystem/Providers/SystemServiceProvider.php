<?php

namespace Modules\NexcoreSystem\Providers;

use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    protected $moduleName = 'NexcoreSystem';
    protected $moduleNameLower = 'nexcore_system';

    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->publishAssets();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $configPath = module_path($this->moduleName, 'Config/config.php');
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, $this->moduleNameLower);
        }
    }

    protected function registerViews()
    {
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    protected function publishAssets()
    {
        $assetPath = module_path($this->moduleName, 'Resources/assets');

        if (!is_dir($assetPath)) {
            return;
        }

        $publicBase = public_path('nexcore');

        $assetDirs = [
            'system_messages',
            'system_sidebar',
            'system_titlebar',
            'system_actionbar',
            'system_header',
            'system_body',
            'system_footer',
            'system_drawer',
            'branding',
        ];

        foreach ($assetDirs as $dir) {
            $source = $assetPath . '/' . $dir;
            $destination = $publicBase . '/' . $dir;

            if (is_dir($source)) {
                $this->publishes([
                    $source => $destination,
                ], ['nexcore-system-assets', 'nexcore-assets']);
            }
        }
    }

    public function provides()
    {
        return [];
    }
}
