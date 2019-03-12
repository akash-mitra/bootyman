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

Auth::routes( ['register' => false]);
Route::get('/', 'HomeController@welcome')->name('welcome');
Route::get('/home', 'HomeController@index')->name('home')->middleware('auth');

Route::get('/snapshots', 'HomeController@snapshots')->name('snapshots')->middleware('auth');
Route::get('/snapshots/{snapshot_id}', 'HomeController@snapshot')->name('snapshots.snapshot')->middleware('auth');
Route::get('/booties', 'HomeController@booties')->name('booties')->middleware('auth');
Route::get('/cloud', 'HomeController@cloud')->name('cloud')->middleware('auth');
Route::get('/token', 'HomeController@token')->name('token')->middleware('auth');


Route::get('/password', 'HomeController@passwordShow')->name('password.show')->middleware('auth');
Route::post('/password-change', 'HomeController@passwordChange')->name('password.change')->middleware('auth');