<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    if (auth()->guest()) return view('Login', request()->all());
    else return redirect()->intended('/employee/'.auth()->user()->employee->id.'/edit');
});
Route::post('/login', [AccountController::class, 'show']);
Route::post('/forgot', [AccountController::class, 'update']);
Route::get('password/reset/{token}', [AccountController::class, 'create'])->name('password.reset');
Route::post('password/reset', [AccountController::class, 'edit']);
Route::post('/register', [AccountController::class, 'store']);
Route::get('/logout', [AccountController::class, 'destroy']);

Route::get('/employee', [EmployeeController::class, 'index']);
Route::get('/employee/create', [EmployeeController::class, 'create']);
Route::get('/employee/{id}/edit', [EmployeeController::class, 'edit']);
Route::post('/employee', [EmployeeController::class, 'store']);
Route::put('/employee/{id}', [EmployeeController::class, 'update']);
Route::delete('/employee', [EmployeeController::class, 'destroy']);
Route::get('/colleague', [EmployeeController::class, 'show']);
Route::post('/colleague', [EmployeeController::class, 'show']);
Route::get('/team', [EmployeeController::class, 'team']);
Route::post('/recruit', [EmployeeController::class, 'recruit']);
Route::post('/expel', [EmployeeController::class, 'expel']);

Route::get('/feedback', [FeedbackController::class, 'index']);
Route::get('/feedback/create', [FeedbackController::class, 'create']);
Route::get('/read/{id}', [FeedbackController::class, 'update']);
Route::post('/feedback', [FeedbackController::class, 'store']);

Route::get('/document', [DocumentController::class, 'index']);
Route::get('/document/create', [DocumentController::class, 'create']);
Route::get('/document/{id}', [DocumentController::class, 'show']);
Route::post('/document', [DocumentController::class, 'store']);
Route::put('/document/{id}', [DocumentController::class, 'update']);

Route::get('/payment', [PaymentController::class, 'index']);