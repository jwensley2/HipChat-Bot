<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    abort(404);
});


Route::get('addon/capabilities', ['as' => 'capabilities', 'uses' => 'Hipchat@capabilities']);
Route::post('addon/install', ['as' => 'install', 'uses' => 'Hipchat@install']);

Route::post('addon/command', 'Hipchat@command');

Route::controllers([
    'auth'     => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
