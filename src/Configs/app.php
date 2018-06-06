<?php

return [
	'default_locale' => app()->getLocale(),
	'locales' => [
		'cs' => 'cs',
		'en' => 'en',
	],
	'model' => \Bistroagency\Imaginator\Models\Imaginator::class,
	'routes' => [
		'prefix' => 'imaginator',
		'as' => 'imaginator.',
		'middlewares' => [
			'web',
		],
	],
	'storage' => [
		'tempDestination' => '/storage/imaginator/tmp',
		'destination' => '/storage/imaginator',
	],
	'densities' => [
		'regular' => [
			'scale' => 1,
			'suffix' => null,
		],
		'retina' => [
			'scale' => 2,
			'suffix' => '@2',
		],
	],
	'anchor_points' => [
		'tl' => 'top-left',
		't' => 'top',
		'tr' => 'top-right',
		'l' => 'left',
		'c' => 'center',
		'r' => 'right',
		'bl' => 'bottom-left',
		'b' => 'bottom',
		'br' => 'bottom-right',
	],
	/*
	 * Any attributes in this array will be allowed onto the <picture> tag
	 * generated by the generateImagiantorPicture static function.
	 */
	'allowedPictureAttributes' => [
		'class',
		'id',
	],
	/*
	 * gd|imagick
	 */
	'intervention_driver' => 'imagick',
];