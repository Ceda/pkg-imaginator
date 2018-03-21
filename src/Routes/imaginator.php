<?php

Route::group([
	'prefix' => config('imaginator.app.routes.prefix'),
	'as' => config('imaginator.app.routes.as'),
	'namespace' => 'Bistroagency\Imaginator',
	'middleware' => config('imaginator.app.routes.middlewares'),
], function () {
	Route::get('/', 'ImaginatorLogic@index')->name('index');
	Route::get('/create/{template}', 'ImaginatorLogic@create')->name('create');
	Route::get('/view/{template}', 'ImaginatorLogic@view')->name('view');
	Route::post('/store', 'ImaginatorLogic@store')->name('store');
	Route::post('/upload', 'ImaginatorLogic@upload')->name('upload');
	Route::delete('/{imaginator}', 'ImaginatorLogic@destroy')->name('destroy');
	Route::delete('/', 'ImaginatorLogic@destroyAllUnused')->name('destroy.allUnused');

	Route::get('dummy-image/{width}x{height}', 'ImaginatorLogic@dummy')->name('dummy-image');
});