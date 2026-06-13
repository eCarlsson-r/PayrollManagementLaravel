<?php

use App\Http\Controllers\Api\PayrollApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Consumed by the Band agent pipeline. All routes are guarded by the
| shared-key `api.key` middleware (BAND_API_KEY in .env).
|
*/

Route::middleware('api.key')->prefix('payroll')->group(function () {
    Route::get('/calculate', [PayrollApiController::class, 'calculate']);
    Route::post('/submit', [PayrollApiController::class, 'submit']);
    Route::post('/flag', [PayrollApiController::class, 'flag']);
});
