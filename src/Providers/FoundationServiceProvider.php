<?php

namespace Orchid\CMS\Providers;

use Illuminate\Support\ServiceProvider;
use Orchid\CMS\Behaviors\Storage\ManyBehaviorStorage;
use Orchid\CMS\Behaviors\Storage\SingleBehaviorStorage;
use Orchid\CMS\Field\FieldStorage;
use Orchid\Platform\Kernel\Dashboard;

class FoundationServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @param Dashboard $dashboard
     */
    public function boot(Dashboard $dashboard)
    {
        $dashboard->registerResource('stylesheets', '/orchid/css/cms.css');
        $dashboard->registerResource('scripts', '/orchid/js/cms.js');

        $dashboard->registerStorage('fields', new FieldStorage);
        $dashboard->registerStorage('pages', new SingleBehaviorStorage);
        $dashboard->registerStorage('posts', new ManyBehaviorStorage);

        $this->registerCode();
        $this->registerDatabase();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerPublic();

        $this->registerProviders();
    }

    /**
     * Register types.
     */
    protected function registerCode()
    {
        $this->publishes([
            CMS_PATH . '/resources/stubs/behaviors/DemoPost.stub' => app_path('/Core/Behaviors/Many/DemoPost.php'),
            CMS_PATH . '/resources/stubs/behaviors/DemoPage.stub' => app_path('/Core/Behaviors/Single/DemoPage.php'),
            CMS_PATH . '/resources/stubs/widgets/AdvertisingWidget.stub' => app_path('/Http/Widgets/AdvertisingWidget.php'),
        ]);
    }

    /**
     * Register migrate.
     */
    protected function registerDatabase()
    {
        $this->publishes([
            CMS_PATH . '/resources/stubs/database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $this->loadTranslationsFrom(CMS_PATH . '/resources/lang', 'cms');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            CMS_PATH . '/config/cms.php' => config_path('cms.php'),
            CMS_PATH . '/config/scout.php' => config_path('scout.php'),
        ]);

        $this->mergeConfigFrom(CMS_PATH . '/config/cms.php', 'cms');
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/vendor/orchid/dashboard';
        }, config('view.paths')), [
            CMS_PATH . '/resources/views',
        ]), 'cms');

        if (!config('platform.install')) {
            $this->publishes([
                CMS_PATH . '/resources/stubs/views/welcome.blade.php' => base_path('resources/views/welcome.blade.php'),
            ]);
        }
    }

    /**
     * Register public.
     */
    protected function registerPublic()
    {
        $this->publishes([
            CMS_PATH . '/public/' => public_path('orchid'),
        ], 'public');
    }

    public function registerProviders()
    {
        foreach ($this->provides() as $provide) {
            $this->app->register($provide);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            RouteServiceProvider::class,
            ConsoleServiceProvider::class,
            PermissionServiceProvider::class,
            EventServiceProvider::class,
            MenuServiceProvider::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        if (!defined('CMS_PATH')) {
            define('CMS_PATH', realpath(__DIR__ . '/../../'));
        }
    }
}
