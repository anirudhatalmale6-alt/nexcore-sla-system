<?php

use Illuminate\Support\Facades\Route;

Route::prefix('nexcore/addresses')->name('nexcore.addresses.')->group(function () {

    Route::get('/',           'AddressController@index')->name('index');
    Route::get('/create',     'AddressController@create')->name('create');
    Route::post('/',          'AddressController@store')->name('store');
    Route::get('/{id}/edit',  'AddressController@edit')->name('edit');
    Route::put('/{id}',       'AddressController@update')->name('update');
    Route::delete('/{id}',    'AddressController@destroy')->name('destroy');
    Route::post('/{id}/toggle', 'AddressController@toggle')->name('toggle');

    // AJAX endpoints
    Route::get('/search-registry', 'AddressController@searchRegistry')->name('search-registry');

    // Contextual routes (called from other modules with linkable_type + linkable_id)
    Route::get('/link/{linkableType}/{linkableId}',       'AddressController@createForEntity')->name('create-for-entity');
    Route::post('/link/{linkableType}/{linkableId}',      'AddressController@storeForEntity')->name('store-for-entity');
    Route::get('/link/{linkId}/edit',                      'AddressController@editLink')->name('edit-link');
    Route::put('/link/{linkId}',                           'AddressController@updateLink')->name('update-link');
    Route::delete('/link/{linkId}',                        'AddressController@destroyLink')->name('destroy-link');
    Route::post('/link/{linkId}/toggle',                   'AddressController@toggleLink')->name('toggle-link');
});
