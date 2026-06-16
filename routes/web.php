<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\WorkflowController;

Route::get('/', function () {
    if (auth()->guest()) return view('Login', request()->all());
    else return redirect()->intended('/employee/'.auth()->user()->employee->id.'/edit');
})->name('login');

Route::middleware('guest')->group(function() {
    Route::controller(AccountController::class)->group(function() {
        Route::post('/login', 'show');
        Route::post('/forgot', 'update');
        Route::get('password/reset/{token}', 'create')->name('password.reset');
        Route::post('password/reset', 'edit');
        Route::post('/register', 'store');
    });
});

Route::middleware('auth')->group(function() {
    Route::get('/payment', [PaymentController::class, 'index']);
    Route::get('/payslip', [PayslipController::class, 'index']);
    Route::get('/payslip/{id}/download', [PayslipController::class, 'download']);

    Route::controller(AccountController::class)->group(function() {
        Route::get('/logout', 'destroy');
        Route::post('/push', 'index');
    });

    Route::controller(EmployeeController::class)->group(function() {
        Route::get('/employee/{id}/edit', 'edit');
        Route::put('/employee/{id}', 'update');
        Route::get('/colleague', 'show');
        Route::post('/colleague', 'show');
    });
});

Route::middleware(['auth', 'type:Employee,Manager'])->group(function() {
    Route::controller(FeedbackController::class)->group(function() {
        Route::get('/feedback/create', 'create');
        Route::post('/feedback', 'store');
    });

    Route::controller(DocumentController::class)->group(function() {
        Route::get('/document/create', 'create');
        Route::post('/document', 'store');
    });
});

Route::middleware(['auth', 'type:Admin,Manager'])->group(function() {
    Route::controller(EmployeeController::class)->group(function() {
        Route::get('/team', [EmployeeController::class, 'team']);
        Route::post('/recruit', [EmployeeController::class, 'recruit']);
        Route::post('/expel', [EmployeeController::class, 'expel']);
    });

    Route::controller(FeedbackController::class)->group(function() {
        Route::get('/feedback', 'index');
        Route::get('/feedback/{id}', 'show');
        Route::get('/read/{id}', 'update');
    });

    Route::controller(DocumentController::class)->group(function() {
        Route::get('/document', 'index');
        Route::get('/document/{id}', 'show');
        Route::put('/document/{id}', 'update');
    });

    Route::controller(WorkflowController::class)->group(function() {
        Route::get('/workflow', 'index');
        Route::post('/workflow/flag/{id}', 'resolveFlag');
    });
});

Route::middleware(['auth', 'type:Admin'])->group(function() {
    Route::controller(EmployeeController::class)->group(function() {
        Route::get('/employee', 'index');
        Route::get('/employee/create', 'create');
        Route::post('/employee', 'store');
        Route::delete('/employee', 'destroy');
    });

    Route::controller(PaymentController::class)->group(function() {
        Route::get('/payment/create', 'create');
        Route::post('/payment', 'store');
    });
});