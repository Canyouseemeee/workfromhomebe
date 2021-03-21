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
Route::post('/getcheckin','ApiController@getcheckin');

Route::post('/history','ApiController@gethistorycheckin');
Route::post('/historybetween','ApiController@gethistorybetweencheckin');

Route::get('/getdepartment','ApiController@getdepartment');
Route::post('/getuser','ApiController@getuser');

Route::post('/posttask','ApiController@posttask');
Route::post('/gettask','ApiController@gettask');
Route::post('/updatetask','ApiController@updatetask');

Route::post('/getassigntask','ApiController@getassigntask');
Route::post('/poststatustask','ApiController@poststatustask');
Route::post('/postsubmittask','ApiController@postsubmittask');
Route::post('/postretask','ApiController@postretask');






