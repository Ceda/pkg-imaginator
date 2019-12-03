<?php

namespace Bistroagency\Imaginator;

use Bistroagency\Imaginator\Commands\CleanFiles;
use Bistroagency\Imaginator\Commands\Refresh;
use Bistroagency\Imaginator\Repositories\ImaginatorRepository;
use Illuminate\Foundation\AliasLoader;
use Intervention\Image\Facades\Image;
use Illuminate\Support\ServiceProvider;

class ImaginatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        /*
        * Register helpers if needed.
        */
        require_once __DIR__ . '/Helpers/common.php';
        require_once __DIR__ . '/Helpers/imaginator.php';
        require_once __DIR__ . '/Helpers/compress_png.php';

        if (!function_exists('push_flash')) {
            require_once __DIR__ . '/Helpers/alerts.php';
        }
        if (!function_exists('slugify')) {
            require_once __DIR__ . '/Helpers/remove_accents.php';
        }

        /*
        * Register Imaginator routes.
        */
        $this->loadRoutesFrom(__DIR__ . '/Routes/imaginator.php');

        /*
        * Register migrations.
        */
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');

        /*
        * Register views.
        */
        $this->loadViewsFrom(__DIR__ . '/Resources/views/', 'imaginator');

        /*
        * Register Imaginator console commands
        */
        if ($this->app->runningInConsole()) {
            $this->commands([
                Refresh::class,
                CleanFiles::class,
            ]);
        }

        /*
         * Set intervention driver
         */
        Image::configure([
            'driver' => config('imaginator.app.intervention_driver', 'imagick')
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*
        * Bind Imaginator class
        */
        $this->app->singleton('imaginator', config('imaginator.app.model'));

        /*
        * Publish configs.
        */
        $this->publishes([
            __DIR__ . '/Configs' => config_path('/imaginator')
        ], 'imaginator-configs');

        /*
        * Publish views.
        */
        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/imaginator'),
        ], 'imaginator-views');

        /*
        * Publish assets.
        */
        $this->publishes([
            __DIR__ . '/assets' => base_path('../assets/imaginator')
        ], 'imaginator-assets');

        /*
        * Publish migrations.
        */
        $this->publishes([
            __DIR__ . '/Database/migrations' => base_path('/database/migrations')
        ], 'imaginator-migrations');

        /*
        * Register packaged dependencies.
        */
        $this->app->register('Intervention\Image\ImageServiceProvider');

        /*
         * Create aliases for the dependencies.
         */
        $loader = AliasLoader::getInstance();
        $loader->alias('Image', Image::class);

        /*
         * Register Imaginator repository for getters
         */
        $this->app->singleton(
            'ImaginatorRepository',
            ImaginatorRepository::class
        );
    }
}
