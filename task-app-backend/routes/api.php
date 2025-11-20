<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
  Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
  // ===== /tasks =====
  Route::prefix('tasks')->group(function () {
    Route::get('dates', [TaskController::class, 'dates']);
    Route::patch('order', [TaskController::class, 'updateOrder']);
  });
  Route::apiResource('tasks', TaskController::class);
});
