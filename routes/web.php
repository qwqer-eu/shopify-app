<?php

use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', [ViewController::class, 'welcome'])
        ->name('home');

    Route::post('create-location', [OrderController::class, 'location_create']);

    Route::get('get-orders', [OrderController::class, 'get_orders']);

    Route::post('process-order', [OrderController::class, 'process_order']);

    Route::get('order-details', [OrderController::class, 'get_order_details']);

    Route::get('get-delivery-orders', [OrderController::class, 'get_delivery_orders']);

    Route::post('update-api-details', [SettingsController::class, 'update_details']);

    Route::get('api-details', [SettingsController::class, 'get_details']);
});

Route::post('add-rates', [DeliveryController::class, 'add_shipping_rates'])
    ->name('add-rates');

Route::get('privacy-policy', [OrderController::class, 'privacy_policy'])
    ->name('privacy-policy');
