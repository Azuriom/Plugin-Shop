<?php

use Azuriom\Plugin\Shop\Controllers\CartController;
use Azuriom\Plugin\Shop\Controllers\CategoryController;
use Azuriom\Plugin\Shop\Controllers\CouponController;
use Azuriom\Plugin\Shop\Controllers\GiftcardController;
use Azuriom\Plugin\Shop\Controllers\OfferController;
use Azuriom\Plugin\Shop\Controllers\PackageController;
use Azuriom\Plugin\Shop\Controllers\PaymentController;
use Azuriom\Plugin\Shop\Controllers\ProfileController;
use Azuriom\Plugin\Shop\Controllers\SubscriptionController;
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

Route::get('/', [CategoryController::class, 'index'])->name('home');
Route::resource('categories', CategoryController::class)->only('show')->scoped([
    'category' => 'slug',
]);

Route::resource('packages', PackageController::class)->only('show');

Route::prefix('packages/{package}')->name('packages.')->middleware('auth')->group(function () {
    Route::post('/buy', [PackageController::class, 'buy'])->name('buy');
    Route::get('/options', [PackageController::class, 'showVariables']);
    Route::post('/options', [PackageController::class, 'buy'])->name('variables');
    Route::get('/files/{file}', [PackageController::class, 'downloadFile'])->name('file');
});

Route::prefix('offers')->name('offers.')->middleware('verified')->group(function () {
    Route::get('/', [OfferController::class, 'selectPayment'])->name('select');
    Route::get('/{gateway:type}', [OfferController::class, 'buy'])->name('buy');
    Route::post('/{offer:id}/{gateway:type}', [OfferController::class, 'pay'])->name('pay');
});

Route::prefix('cart')->name('cart.')->middleware('auth')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'update'])->name('update');
    // TODO Match multiple methods is not really good here...
    Route::match(['GET', 'POST'], '/remove/{package}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/payment', [CartController::class, 'payment'])->name('payment')->middleware('auth');

    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::post('/add', [CouponController::class, 'add'])->name('add');
        Route::post('/remove/{coupon}', [CouponController::class, 'remove'])->name('remove');
        Route::post('/clear', [CouponController::class, 'clear'])->name('clear');
    });

    Route::prefix('giftcards')->name('giftcards.')->group(function () {
        Route::post('/add', [GiftcardController::class, 'add'])->name('add');
        Route::post('/remove/{giftcard}', [GiftcardController::class, 'remove'])->name('remove');
    });
});

Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::prefix('/{package}')->middleware(['auth', 'verified'])->group(function () {
        Route::post('/', [SubscriptionController::class, 'selectGateway'])->name('select');
        Route::post('/{gateway:type}', [SubscriptionController::class, 'subscribe'])->name('subscribe')->withoutScopedBindings();
    });

    Route::delete('/{subscription}', [SubscriptionController::class, 'cancel'])->middleware('auth')->name('destroy');
});

Route::prefix('payments')->name('payments.')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/payment', [PaymentController::class, 'payment'])->name('payment');
        Route::post('/{gateway:type}/pay', [PaymentController::class, 'pay'])->name('pay');
    });

    Route::get('/{gateway:type}/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/{gateway:type}/failure', [PaymentController::class, 'failure'])->name('failure');
});

Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth')->name('profile');

Route::post('/giftcards/add', [GiftcardController::class, 'add'])->middleware('auth')->name('giftcards.add');
