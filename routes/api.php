<?php

use Azuriom\Plugin\Shop\Controllers\Api\PaymentController;
use Azuriom\Plugin\Shop\Controllers\Api\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your plugin. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/azlink', [ShopController::class, 'index'])->middleware('server.token');
Route::any('/payments/{gateway:type}/notification/{id?}', [PaymentController::class, 'notification'])->name('payments.notification');
