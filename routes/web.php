<?php

use App\Http\Controllers\AuthController;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'jumlahBuku' => Buku::count(),
        'peminjamanAktif' => Peminjaman::where('status', 'Dipinjam')->count(),
        'jumlahAnggota' => User::where('role', 'user')->count(),
    ]);
})->middleware('auth')->name('dashboard');

Route::get('/admin', function () {
    return redirect()->route('users.index');
})->middleware(['auth', 'role:admin']);

Route::get('/anggota', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'role:user']);
