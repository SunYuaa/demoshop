<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});




//weixin
Route::get('/weixin/valid','Weixin\WxController@valid');

Route::post('/weixin/valid','Weixin\WxController@event');

Route::get('/weixin/access_token','Weixin\WxController@getAccessToken');
