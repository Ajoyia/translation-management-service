<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\TranslationExportController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('translations')->group(function (): void {
        Route::get('export', [TranslationExportController::class, 'export']);
        Route::get('export/all', [TranslationExportController::class, 'exportAll']);
        Route::post('cache/clear', [TranslationExportController::class, 'clearCache']);
    });

    Route::apiResource('translations', TranslationController::class);
});
