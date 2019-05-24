<?php

Route::group([
	'prefix' => config('imaginator.app.routes.prefix'),
	'as' => config('imaginator.app.routes.as'),
	'namespace' => 'Bistroagency\Imaginator',
	'middleware' => config('imaginator.app.routes.middlewares'),
], static function () {
	Route::get('/', 'ImaginatorController@index')->name('index');
	Route::get('/create/{template}', 'ImaginatorController@create')->name('create');
	Route::get('/view/{template}', 'ImaginatorController@view')->name('view');
	Route::get('/templates', 'ImaginatorController@templates')->name('templates');
	Route::get('/get-lazyload-object/{imaginator}', 'ImaginatorController@getLazyloadObject')->name('get-lazyload-object');
	Route::post('/store', 'ImaginatorController@store')->name('store');
	Route::post('/upload', 'ImaginatorController@upload')->name('upload');
	Route::delete('/{imaginator}', 'ImaginatorController@destroy')->name('destroy');
	Route::delete('/', 'ImaginatorController@destroyAllUnused')->name('destroy.allUnused');

	Route::get('dummy-image/{width}x{height}', 'ImaginatorController@generateDummyImage')->name('dummy-image');
});