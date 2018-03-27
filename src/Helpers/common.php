<?php

if (!function_exists('asset_versioned')) {
	function asset_versioned($path)
	{
		if (!\File::exists(public_path($path))) {
			return url($path);
		}

		return url($path) . '?' . \File::lastModified(public_path($path));
	}
}

if (!function_exists('body_class')) {
	function body_class()
	{
		$route = \Route::currentRouteName();
		$route = explode('.', $route);

		if (!is_array($route)) {
			return null;
		}

		$locale = array_pull($route, 0);

		return 'page--' . $locale . ' page--' . implode('-', $route) . ' env--' . app()->environment();
	}
}

if (!function_exists('dummy_image')) {
	function dummy_image($width, $height)
	{
		return route(config('imaginator.app.routes.as') . 'dummy-image', ['width' => $width, 'height' => $height]);
	}
}

if (!function_exists('route_raw')) {
	function route_raw($name)
	{
		$route = app('router')->getRoutes()->getByName($name);

		if (!$route) {
			return null;
		}

		return url($route->uri);
	}
}

if (!function_exists('locale')) {
	function locale()
	{
		return app()->getLocale();
	}
}

if (!function_exists('d')) {
	function d()
	{
		array_map(function ($x) {
			(new Illuminate\Support\Debug\Dumper)->dump($x);
		}, func_get_args());
	}
}