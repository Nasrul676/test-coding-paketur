<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\EmployeeController;

Route::get('/', function(){
    return 'Build a Restfull API with Laravel';
});

//public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware(['auth:api'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

//protected routes
Route::middleware(['auth:api'])->group(function () {
    Route::middleware('checkRole:super_admin,manager')->group(function () {
        Route::apiResource('company', CompanyController::class)->except(['create', 'edit']);
    });

    Route::middleware('checkRole:,manager')->group(function () {
        Route::apiResource('manager', ManagerController::class)->except(['store', 'create', 'edit']);
    });

    Route::middleware('checkRole:manager,employee')->group(function () {
        Route::apiResource('employee', EmployeeController::class)->except(['create', 'edit']);
    });
});

