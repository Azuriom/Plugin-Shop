<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your plugin. These
| routes are loaded by the RouteServiceProvider of your plugin within
| a group which contains the "web" middleware group and your plugin name
| as prefix. Now create something great!
|
*/

Route::get('/', 'CategoryController@index')->name('home');
Route::resource('categories', 'CategoryController')->only('show');

Route::resource('packages', 'PackageController')->only('show');
Route::post('/packages/{package}/buy', 'PackageController@buy')->name('packages.buy')->middleware('auth');

Route::prefix('offers')->name('offers.')->middleware('auth')->group(function () {
    Route::get('/', 'OfferController@selectPayment')->name('select');
    Route::get('/{gateway:type}', 'OfferController@buy')->name('buy');
    Route::post('/{offer}/{gateway:type}', 'OfferController@pay')->name('pay');
});

Route::prefix('cart')->name('cart.')->middleware('auth')->group(function () {
    Route::get('/', 'CartController@index')->name('index');
    Route::get('/remove/{package}', 'CartController@remove')->name('remove');
    Route::post('/clear', 'CartController@clear')->name('clear');
    Route::post('/checkout', 'CartController@checkout')->name('checkout');
    Route::post('/payment', 'CartController@payment')->name('payment')->middleware('auth');
});

Route::prefix('payments')->name('payments.')->middleware('auth')->group(function () {
    Route::get('/payment', 'PaymentController@payment')->name('payment');
    Route::post('{gateway:type}/pay', 'PaymentController@pay')->name('pay');
    Route::get('{gateway:type}/success/', 'PaymentController@success')->name('success');
    Route::get('{gateway:type}/failure/', 'PaymentController@failure')->name('failure');
});
