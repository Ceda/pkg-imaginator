<?php

if (!function_exists('get_imaginator')) {
	function get_imaginator(int $id)
	{
		return \Bistroagency\Imaginator\Facades\Imaginator::getImaginator($id);
	}
}

if (!function_exists('get_imaginator_object')) {
	function get_imaginator_object(int $id)
	{
		return \Bistroagency\Imaginator\Facades\Imaginator::getImaginator($id)->getLazyloadObject(locale());
	}
}

if (!function_exists('get_or_create_imaginator')) {
	//TODO better indentifier for image instead of $aliasOrIdOrPath
	function get_or_create_imaginator($resources, string $templateName, string $anchorPoint = 'c')
	{
		return \Bistroagency\Imaginator\Models\Imaginator::getOrCreateImaginator($resources, $templateName, $anchorPoint);
	}
}

if (!function_exists('generate_imaginator_picture')) {
	function generate_imaginator_picture($imaginator, string $locale = null, array $attributes = [])
	{
		return \Bistroagency\Imaginator\Facades\Imaginator::generateImaginatorPicture($imaginator, $locale, $attributes);
	}
}

if (!function_exists('make_imaginator_path')) {
	function make_imaginator_path(array $parameters, $glue = '/')
	{
		return implode($glue, $parameters);
	}
}


if (!function_exists('dummy_image')) {
	function dummy_image($width, $height)
	{
		return route(config('imaginator.app.routes.as') . 'dummy-image', ['width' => $width, 'height' => $height]);
	}
}
