<?php

function route_raw($name)
{
	$route = app('router')->getRoutes()->getByName($name);

	if (!$route) {
		return null;
	}

	return url($route->uri);
}