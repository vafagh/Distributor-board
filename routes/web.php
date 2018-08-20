<?php

Auth::routes();

Route::GET('/', 'RideableController@home')->name('home');
Route::GET('/drivers', 'DriverController@index')->name('drivers');
Route::GET('/driver/show/{driver}', 'DriverController@show');
Route::POST('/driver/store', 'DriverController@store')->name('add.driver');
Route::POST('/driver/save', 'DriverController@update')->name('update.driver');
Route::GET('/driver/delete/{driver}', 'DriverController@destroy')->name('destroy.driver');
Route::GET('/driver/{driver}/unassign', 'DriverController@unassign')->name('unassign.driver');
Route::GET('/trucks', 'TruckController@index')->name('trucks');
Route::GET('/truck/show/{truck}', 'TruckController@show');
Route::POST('/truck/store', 'TruckController@store')->name('add.truck');
Route::POST('/truck/save', 'TruckController@update')->name('save.truck');
Route::GET('/rides/', 'RideController@index');
Route::GET('/ride/edit/{ride}', 'RideController@edit')->name('edit.ride');
Route::POST('/ride/save/', 'RideController@update')->name('update.ride');
Route::GET('/ride/delete/{ride}', 'RideController@destroy')->name('destroy.ride');
Route::GET('/ride/create/{rideable}', 'RideController@create')->name('create.ride');
Route::POST('/ride/store/', 'RideController@attach')->name('attach.ride');
Route::GET('/ride/detach/{ride}/{rideable}', 'RideController@detach')->name('detach.ride');
Route::POST('/rideable/store', 'RideableController@store')->name('add.rideable');
Route::POST('/rideable/save', 'RideableController@update')->name('update.rideable');
Route::GET('/rideable/show/{rideable}', 'RideableController@show');
Route::GET('/rideable/delete/{rideable}', 'RideableController@destroy')->name('destroy.rideable');
Route::GET('/rideable/location/{location}', 'RideableController@list');
Route::GET('/rideable/{rideable}/{status}', 'RideableController@status')->name('set_status.rideable');
Route::GET('/locations', 'LocationController@index')->name('locations');
Route::GET('/location/delete/{location}', 'LocationController@destroy')->name('destroy.location');
Route::POST('/location/save', 'LocationController@update')->name('update.location');
Route::POST('/location/store', 'LocationController@store')->name('add.location');
Route::GET('/location/show/{location}', 'LocationController@show');
Route::GET('/fillups', 'FillupController@index')->name('fillups');
Route::POST('/fillup/store/', 'FillupController@store')->name('add.fillup');
Route::GET('/fillup/delete/{fillup}', 'FillupController@destroy')->name('destroy.fillup');
Route::GET('/fillup/show/{fillup}', 'FillupController@show')->name('show.fillup');
Route::POST('/fillup/save', 'FillupController@update')->name('update.fillup');
Route::GET('/users', 'UserController@index')->name('users');
Route::GET('/user/show/{user}', 'UserController@show');
Route::POST('/user/store', 'UserController@store')->name('add.user');
Route::POST('/user/save', 'UserController@update')->name('update.user');
Route::GET('/search', 'HomeController@find')->name('search');

Route::GET('/{type}', 'RideableController@list');
