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


/*
|--------------------------------------------------------------------------
| Snapshot Related Routes
|--------------------------------------------------------------------------
*/

// create a new image from the source control code
Route::post('snapshots/create/image', 'SnapshotController@createImage')->name('snapshots.create.image');

// create a new snapshot from an existing image
Route::post('snapshots/create/snapshot', 'SnapshotController@createSnapshot')->name('snapshots.create.snapshot');

// triggers the whole process of refreshing snapshots - which include building image, taking snapshot and deleting the image
Route::post('snapshots/refresh', 'SnapshotController@refresh')->name('snapshots.refresh');

// all deletion related routes
Route::delete('snapshots/delete/image', 'SnapshotController@deleteImage')->name('snapshots.delete.image');
Route::delete('snapshots/delete/snapshot', 'SnapshotController@deleteSnapshot')->name('snapshots.delete.snapshot');
Route::delete('snapshots/delete/all', 'SnapshotController@deleteAll')->name('snapshots.delete');

// retrieves the id of the latest snapshot
Route::get('snapshots/latest', 'SnapshotController@latest')->name('snapshots.latest');


/*
|--------------------------------------------------------------------------
| Booty Related Routes
|--------------------------------------------------------------------------
*/
Route::post('booties/provision', 'BootyController@provision')->name('booties.provision');