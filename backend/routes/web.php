<?php

use App\Http\Controllers\AppBrandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ScreeningSubmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
  Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
  Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
  Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
  Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

  Route::prefix('education')->name('education.')->group(function () {
    Route::get('/', [EducationController::class, 'index'])->name('index');
    Route::get('/{menu:slug}', [EducationController::class, 'showMenu'])->name('menus.show');
    Route::get('/{menu:slug}/{item}', [EducationController::class, 'showContent'])->name('contents.show');
    Route::put('/{menu:slug}/{item}', [EducationController::class, 'updateContent'])
      ->name('contents.update')
      ->middleware('role:admin');
  });

  Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/splash', [AppBrandingController::class, 'editSplash'])->name('splash.edit');
    Route::post('/splash', [AppBrandingController::class, 'updateSplash'])->name('splash.update');
    Route::delete('/splash', [AppBrandingController::class, 'destroySplash'])->name('splash.destroy');
  });

  Route::resource('users', UserController::class)->except(['show'])->middleware('role:admin');

  Route::middleware('role:admin')->prefix('screening-submissions')->name('screening-submissions.')->group(function () {
    Route::get('/', [ScreeningSubmissionController::class, 'index'])->name('index');
    Route::get('/{screeningSubmission}', [ScreeningSubmissionController::class, 'show'])->name('show');
  });
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
