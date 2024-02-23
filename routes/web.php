<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InventoryController;
use App\Models\Inventory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false,
//        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // begin::Inventory Routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store')->middleware('admin');
    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update')->middleware('admin');
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy')->middleware('admin');
    Route::delete('/inventory/image/{inventory}', [InventoryController::class, 'removeImage'])->name('inventory.removeImage')->middleware('admin');
    // end::Inventory Routes

    Route::get('/stock-management', function () {
        return Inertia::render('StockManagement');
    })->name('stock-management');

    // begin::Admin Routes
    Route::get('/reports', function () {
        return Inertia::render('Reports');
    })->name('reports')->middleware('admin');
    // end::Admin Routes

    // begin::Staff Routes
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index')->middleware('staff');
    Route::post('/delivery', [DeliveryController::class, 'store'])->name('delivery.store')->middleware('staff');
    Route::put('/delivery/{delivery}', [DeliveryController::class, 'update'])->name('delivery.update')->middleware('staff');
    Route::delete('/delivery/{delivery}', [DeliveryController::class, 'destroy'])->name('delivery.destroy')->middleware('staff');
    // end::Staff Routes
});
