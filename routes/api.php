<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

// Baca: publik
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);
Route::get('/members', [MemberController::class, 'index']);
Route::get('/members/{id}', [MemberController::class, 'show']);
Route::get('/loans', [LoanController::class, 'index']);
Route::get('/stats', [StatsController::class, 'index']);

// Tulis: wajib header X-API-Key
Route::middleware(['api.key'])->group(function () {
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);

    Route::post('/loans', [LoanController::class, 'store']);
    Route::put('/loans/{id}/kembalikan', [LoanController::class, 'kembalikan']);
});
