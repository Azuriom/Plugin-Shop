<?php

use Azuriom\Plugin\Shop\Controllers\Admin\CategoryController;
use Azuriom\Plugin\Shop\Controllers\Admin\CouponController;
use Azuriom\Plugin\Shop\Controllers\Admin\DiscountController;
use Azuriom\Plugin\Shop\Controllers\Admin\GatewayController;
use Azuriom\Plugin\Shop\Controllers\Admin\GiftcardController;
use Azuriom\Plugin\Shop\Controllers\Admin\OfferController;
use Azuriom\Plugin\Shop\Controllers\Admin\PackageController;
use Azuriom\Plugin\Shop\Controllers\Admin\PaymentController;
use Azuriom\Plugin\Shop\Controllers\Admin\PurchaseController;
use Azuriom\Plugin\Shop\Controllers\Admin\SettingController;
use Azuriom\Plugin\Shop\Controllers\Admin\StatisticsController;
use Azuriom\Plugin\Shop\Controllers\Admin\SubscriptionController;
use Azuriom\Plugin\Shop\Controllers\Admin\VariableController;
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
Route::middleware('can:shop.settings')->prefix('settings')->group(function () {
    Route::get('/', [SettingController::class, 'show'])->name('settings');
    Route::post('/', [SettingController::class, 'save'])->name('settings.save');
});

Route::middleware('can:shop.packages')->group(function () {
    Route::resource('packages', PackageController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    Route::resource('variables', VariableController::class)->except('show');

    Route::post('/packages/update-order', [PackageController::class, 'updateOrder'])->name('packages.update-order');
    Route::post('/packages/{package}/duplicate',
        [PackageController::class, 'duplicate'])->name('packages.duplicate');
});

Route::middleware('can:shop.payments')->group(function () {
    Route::resource('payments', PaymentController::class)->only(['index', 'show', 'create', 'store']);
    Route::resource('purchases', PurchaseController::class)->only('index');
    Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'show', 'destroy']);

    Route::prefix('stats')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('statistics');
        Route::get('/packages/{package}', [StatisticsController::class, 'showPackage'])->name('statistics.package');
    });
});

Route::middleware('can:shop.gateways')->group(function () {
    Route::resource('gateways', GatewayController::class)->except(['show', 'create']);
    Route::resource('offers', OfferController::class)->except('show');

    Route::post('/gateways/positions', [GatewayController::class, 'updateOrder'])->name('gateways.positions');
    Route::get('/gateways/create/{type}', [GatewayController::class, 'create'])->name('gateways.create');
});

Route::middleware('can:shop.promotions')->group(function () {
    Route::resource('coupons', CouponController::class)->except('show');
    Route::resource('giftcards', GiftcardController::class)->except('show');
});

Route::resource('discounts', DiscountController::class)->middleware('can:shop.giftcards')->except('show');
