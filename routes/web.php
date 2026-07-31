<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Catalog\Categories\CategoryIndex;
use App\Livewire\Catalog\Products\ProductIndex;
use App\Livewire\Dashboard\Overview;
use App\Livewire\Onboarding\CreateBusiness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ---- Guest Routes -----------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login',    Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// ---- Authenticated + Tenant Routes ------------------------------------------
Route::middleware(['auth', 'tenant'])->group(function () {

    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/onboarding', CreateBusiness::class)->name('onboarding');
    Route::get('/dashboard',  Overview::class)->name('dashboard');

    // Phase 2A — Catalog Management
    Route::get('/categories', CategoryIndex::class)->name('categories.index');
    Route::get('/products',   ProductIndex::class)->name('products.index');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
