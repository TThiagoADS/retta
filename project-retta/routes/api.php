<?php

use Illuminate\Support\Facades\Route;
use App\Interfaces\Http\Controllers\User\CreateUserController;
use App\Interfaces\Http\Controllers\Auth\AuthController;

//User routes
Route::post('/user', [CreateUserController::class, 'store']);

//Auth routes
Route::post('/login', [AuthController::class, 'login']);
