<?php

if (!function_exists('get_imaginator')) {
	function get_imaginator(int $id)
	{
		return \Bistroagency\Imaginator\Facades\Imaginator::getImaginator($id);
	}
}

if (!function_exists('generate_imaginator_picture')) {
	function generate_imaginator_picture(int $id, string $locale = null, array $attributes = [])
	{
		return \Bistroagency\Imaginator\Facades\Imaginator::generateImaginatorPicture($id, $locale, $attributes);
	}
}