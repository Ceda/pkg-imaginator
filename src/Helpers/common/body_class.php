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