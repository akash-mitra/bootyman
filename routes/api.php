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

// delete the vm behind a booty
Route::delete('/delete/booty', 'BootyController@deleteBooty')->name('delete.booty');

// delete the image behind a snapshot
Route::delete('/delete/snapshot', 'BootyController@deleteSnapshot')->name('delete.snapshot');

// triggers the whole process of refreshing snapshot
Route::post('/refresh', 'BootyController@rebuild')->name('refresh');




