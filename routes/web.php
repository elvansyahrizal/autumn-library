<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Livewire\BookCatalog;
use App\Livewire\LoanApproval;
use App\Livewire\MyLoans;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Katalog Buku (Bisa dilihat siapa saja, pinjam perlu login anggota)
Route::get('/katalog', BookCatalog::class)->name('catalog.index');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        if (auth()->user()->isAnggota()) {
            return redirect()->route('catalog.index');
        }

        return app(DashboardController::class)->index();
    })->name('dashboard');

    // Khusus Mahasiswa / Anggota
    Route::get('/pinjaman-saya', MyLoans::class)->name('my-loans.index');

    // Khusus Petugas & Admin: Panel Persetujuan & Pengembalian Buku
    Route::get('/persetujuan-sirkulasi', LoanApproval::class)->name('loans.approval');

    // Master Data & Sirkulasi Konvensional
    Route::resource('books', BookController::class);
    Route::resource('members', MemberController::class);
    Route::get('/loans/report', [LoanController::class, 'report'])->name('loans.report');
    Route::resource('loans', LoanController::class);
    Route::put('/loans/{id}/kembalikan', [LoanController::class, 'kembalikan'])
        ->name('loans.kembalikan');

    // Profil & Ganti Password
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::middleware(['admin'])->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
    });
});
