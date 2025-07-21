<?php

use Illuminate\Support\Facades\Route;
use App\Interfaces\Http\Controllers\User\CreateUserController;

//User routes
Route::post('/user', [CreateUserController::class, 'store']);

Route::get('/debug', function () {
    return response()->json(['status' => 'api carregada']);
});

Route::get('/user', function () {
    return ['name' => 'Thiago'];
});
