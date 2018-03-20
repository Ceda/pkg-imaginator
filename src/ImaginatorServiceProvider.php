<?php

namespace Bistroagency\Imaginator;

use Bistroagency\Imaginator\Commands\CleanFiles;
use Bistroagency\Imaginator\Commands\Refresh;
use Illuminate\Support\ServiceProvider;

class ImaginatorServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		/*
		* Register helpers if needed.
		*/
		require_once __DIR__ . '/Helpers/common.php';
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
		$this->app->bind('bistroagency-imaginator', function () {
			return new ImaginatorLogic();
		});

		/*
		* Register default configs.
		*/
		$this->mergeConfigFrom(
			__DIR__ . '/Configs/app.php', 'imaginator.app'
		);
		$this->mergeConfigFrom(
			__DIR__ . '/Configs/schemas.php', 'imaginator.schemas'
		);

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
		* Register packaged ependencies.
		*/
		$this->app->register('Intervention\Image\ImageServiceProvider');

		/*
		 * Create aliases for the dependencies.
		 */
		$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		$loader->alias('Image', 'Intervention\Image\Facades\Image');
	}
}
