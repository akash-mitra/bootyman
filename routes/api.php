<?php

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

// create a new virtual machine from the source control code
Route::post('/create/booty', 'BootyController@createBooty')->name('booties.create');

// create a snapshot from the booty provided
Route::post('/create/snapshot', 'BootyController@createSnapshot')->name('snapshots.create');

// provision a new machine from the latest snapshot of the application
Route::post('/provision', 'BootyController@provision')->name('snapshots.provision');

// assign a domain to a Live booty
Route::post('booties/{booty_id}/domain', 'BootyController@setDomain')->name('booties.domain.create');


// all deletion related routes
Route::delete('delete/booty', 'BootyController@deleteBooty')->name('delete.booty');
Route::delete('delete/snapshot', 'BootyController@deleteSnapshot')->name('delete.snapshot');
// Route::delete('snapshots/delete/all', 'BootyController@deleteAll')->name('snapshots.delete');


// triggers the whole process of refreshing snapshots - which include building image, taking snapshot and deleting the image
Route::post('snapshots/refresh', 'BootyController@refresh')->name('snapshots.refresh');




