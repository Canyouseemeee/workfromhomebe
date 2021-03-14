<?php

use App\Http\Controllers;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

//login
Route::post('/login','ApiController@login');
Route::post('/login-ad','ApiController@loginad');

Route::post('/checktoken','ApiController@checktoken');


Route::post('/checkin','ApiController@postcheckin');
Route::post('/checkout','ApiController@postcheckout');
Route::get('/getcheckin','ApiController@getcheckin');






