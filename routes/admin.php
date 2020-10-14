<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your plugin. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" and "admin" middleware groups. Now create a great admin panel !
|
*/
Route::middleware('can:shop.admin')->group(function () {
    Route::get('/settings', 'SettingController@show')->name('settings');
    Route::post('/settings', 'SettingController@save')->name('settings.save');

    Route::get('/stats', 'StatisticsController@index')->name('statistics');

    Route::resource('categories', 'CategoryController')->except(['index', 'show']);
    Route::resource('packages', 'PackageController')->except('show');
    Route::resource('payments', 'PaymentController')->only(['index', 'show']);
    Route::resource('purchases', 'PurchaseController')->only('index');
    Route::resource('offers', 'OfferController')->except('show');
    Route::resource('coupons', 'CouponController')->except('show');
    Route::resource('discounts', 'DiscountController')->except('show');
    Route::resource('gateways', 'GatewayController')->except(['show', 'create']);

    Route::post('/packages/update-order', 'PackageController@updateOrder')->name('packages.update-order');
    Route::post('/packages/{package}/clone', 'PackageController@clone')->name('packages.clone');
    Route::get('/gateways/create/{type}', 'GatewayController@create')->name('gateways.create');
});
