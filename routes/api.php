<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\ContaController::class, 'login']);
Route::post('/conta', [\App\Http\Controllers\ContaController::class, 'createConta']);
Route::post('/logout', [\App\Http\Controllers\ContaController::class, 'logout']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/conta', [\App\Http\Controllers\ContaController::class, 'getConta']);
    Route::get('/history/{walletId}', [\App\Http\Controllers\OperationController::class, 'getHistory']);
    Route::post('/deposit', [\App\Http\Controllers\OperationController::class, 'deposit']);
    Route::post('/withdraw', [\App\Http\Controllers\OperationController::class, 'withdraw']);
    Route::post('/transfer', [\App\Http\Controllers\OperationController::class, 'transfer']);
});
