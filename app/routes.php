<?php

/** ------------------------------------------
 *  Route model binding
 *  ------------------------------------------
 */
Route::model('user', 'User');

/** ------------------------------------------
 *  Route constraint patterns
 *  ------------------------------------------
 */
Route::pattern('user', '[0-9]+');
//Route::pattern('token', '[0-9a-z]+');

/** ------------------------------------------
 *  Frontend Routes
 *  ------------------------------------------
 */

Route::get('user/login', 'UserController@getLogin');
Route::get('user/{user}', 'UserController@getShow');
Route::controller('user', 'UserController');

Route::get('doctor/{user}', 'UserController@showDoctor');

Route::get('patient/{user}', 'UserController@showPatient');
Route::get('patient/{user}/edit', 'UserController@getEdit');
Route::post('patient/{user}/edit', 'UserController@postEdit');

# Index Page - Last route, no matches
Route::get('/', function(){return View::make('site/home/index');});
