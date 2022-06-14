<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('users', 'App\Http\Controllers\API\UserController@index');
// Route::post('signup', 'App\Http\Controllers\API\UserController@signup');
Route::post('register', 'App\Http\Controllers\API\UserController@register');
Route::post('signin', 'App\Http\Controllers\API\UserController@signin');
Route::get('getBestTherapist', 'App\Http\Controllers\API\UserController@getBestTherapist');
Route::post('bookAppointment', 'App\Http\Controllers\API\UserController@bookAppointment');
Route::get('getAppointments/{id}', 'App\Http\Controllers\API\UserController@getAppointments');
Route::post('adduserdetails', 'App\Http\Controllers\API\UserController@adduserdetails');
// Route::post('login', 'App\Http\Controllers\API\UserController@login');
Route::get('getAllDoctors', 'App\Http\Controllers\API\UserController@getAllDoctors');


