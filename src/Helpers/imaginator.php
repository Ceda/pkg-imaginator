<?php

if(!function_exists('getImaginator')) {
	function getImaginator(int $id)
	{
		return \Bistroagency\Imaginator\Facades\Imaginator::getImaginator($id);
	}
}

if(!function_exists('generateImaginatorPicture')) {
	function generateImaginatorPicture(int $id, string $locale = null, array $attributes = [])
	{
		return \Bistroagency\Imaginator\Facades\Imaginator::generateImaginatorPicture($id, $locale, $attributes);
	}
}