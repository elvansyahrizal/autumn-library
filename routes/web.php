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

// Publik
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Katalog dapat dilihat siapa saja; peminjaman memerlukan login anggota.
Route::get('/katalog', BookCatalog::class)->name('catalog.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        if (auth()->user()->isAnggota()) {
            return redirect()->route('catalog.index');
        }

        return app(DashboardController::class)->index();
    })->name('dashboard');

    // Profil & Ganti Password
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Khusus Mahasiswa / Anggota
    Route::middleware(['role:anggota'])->group(function () {
        Route::get('/pinjaman-saya', MyLoans::class)->name('my-loans.index');
    });

    // Khusus Petugas & Admin: master data dan sirkulasi
    Route::middleware(['role:admin,petugas'])->group(function () {
        Route::get('/persetujuan-sirkulasi', LoanApproval::class)->name('loans.approval');

        Route::resource('books', BookController::class);
        Route::resource('members', MemberController::class);

        Route::get('/loans/report', [LoanController::class, 'report'])->name('loans.report');
        Route::resource('loans', LoanController::class);
        Route::put('/loans/{id}/kembalikan', [LoanController::class, 'kembalikan'])
            ->name('loans.kembalikan');
    });

    // Khusus Admin
    Route::middleware(['admin'])->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
    });
});
