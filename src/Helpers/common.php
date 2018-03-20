<?php

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

function asset_versioned($path)
{
	if (!\File::exists(public_path($path))) {
		return url($path);
	}

	return url($path) . '?' . \File::lastModified(public_path($path));
}

function locale()
{
	return app()->getLocale();
}

function dummy_image($width, $height)
{
	return route('dummy-image', ['width' => $width, 'height' => $height]);
}

function route_raw($name)
{
	$route = app('router')->getRoutes()->getByName($name);

	if (!$route) {
		return null;
	}

	return url($route->uri);
}
