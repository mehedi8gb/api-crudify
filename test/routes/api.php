<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('test')->group(function () {
            Route::get('/', [TestController::class, 'index'])->name('test.index');
            Route::get('show/{slug}', [TestController::class, 'show'])->name('test.show');
            Route::post('store', [TestController::class, 'store'])->name('test.store');
            Route::put('update/{test}', [TestController::class, 'update'])->name('test.update');
            Route::delete('destroy/{test}', [TestController::class, 'destroy'])->name('test.destroy');
        });
