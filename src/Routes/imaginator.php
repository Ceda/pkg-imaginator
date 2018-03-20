<?php

Route::group(['prefix' => 'imaginator', 'namespace' => 'Bistroagency\Imaginator'], function () {
	Route::get('/', 'ImaginatorLogic@index')->name('imaginator.index');
	Route::get('/create/{template}', 'ImaginatorLogic@create')->name('imaginator.create');
	Route::get('/view/{template}', 'ImaginatorLogic@view')->name('imaginator.view');
	Route::post('/store', 'ImaginatorLogic@store')->name('imaginator.store');
	Route::post('/upload', 'ImaginatorLogic@upload')->name('imaginator.upload');
	Route::delete('/{imaginator}', 'ImaginatorLogic@destroy')->name('imaginator.destroy');
	Route::delete('/', 'ImaginatorLogic@destroyAllUnused')->name('imaginator.destroy.allUnused');

	Route::get('dummy-image/{width}x{height}', ['as' => 'dummy-image', 'uses' => 'ImaginatorLogic@dummy']);
});