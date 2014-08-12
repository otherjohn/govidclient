<?php

Route::model('user', 'User');
Route::pattern('user', '[0-9]+');

Route::group(array('prefix' => 'user', 'before' => 'auth'), function(){
	Route::get('/{user}', 'UserController@getShow');
	Route::controller('/', 'UserController');
});

Route::group(array('prefix' => 'doctor', 'before' => 'auth'), function(){
	Route::get('/{user}', 'UserController@showDoctor');
});

Route::group(array('prefix' => 'patient', 'before' => 'auth'), function(){
	Route::get('/{user}', 'UserController@showPatient');
	Route::get('/{user}/edit', 'UserController@getEdit');
	Route::post('/{user}/edit', 'UserController@postEdit');
});

Route::group(array('prefix' => 'login', 'before' => 'guest'), function(){
	Route::get('/', 'UserController@getLogin');
});

Route::get('/logout', 'UserController@getLogout');

Route::get('/', function(){return View::make('site/home/index');});