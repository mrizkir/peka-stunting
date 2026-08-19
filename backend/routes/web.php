<?php

use App\Http\Controllers\AnjuranAnemiaController;
use App\Http\Controllers\AnjuranMenyusuiController;
use App\Http\Controllers\AnjuranStatusGiziController;
use App\Http\Controllers\AnjuranImtController;
use App\Http\Controllers\AnjuranLilaController;
use App\Http\Controllers\AppBrandingController;
use App\Http\Controllers\AppInfoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ScreeningSubmissionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/hapus-akun', 'account-deletion')->name('account-deletion');

Route::middleware('guest')->group(function () {
  Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
  Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
  Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
  Route::post('/register', [AuthController::class, 'register'])->name('register.store');
  Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
  Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
  Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
  Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

  Route::middleware('role:admin')->prefix('anjuran-imt')->name('anjuran-imt.')->group(function () {
    Route::get('/', [AnjuranImtController::class, 'index'])->name('index');
    Route::get('/{menu:slug}', [AnjuranImtController::class, 'edit'])->name('edit');
    Route::put('/{menu:slug}', [AnjuranImtController::class, 'update'])->name('update');
  });

  Route::middleware('role:admin')->prefix('anjuran-lila')->name('anjuran-lila.')->group(function () {
    Route::get('/', [AnjuranLilaController::class, 'index'])->name('index');
    Route::get('/{menu:slug}', [AnjuranLilaController::class, 'edit'])->name('edit');
    Route::put('/{menu:slug}', [AnjuranLilaController::class, 'update'])->name('update');
  });

  Route::middleware('role:admin')->prefix('anjuran-anemia')->name('anjuran-anemia.')->group(function () {
    Route::get('/', [AnjuranAnemiaController::class, 'index'])->name('index');
    Route::get('/{menu:slug}', [AnjuranAnemiaController::class, 'edit'])->name('edit');
    Route::put('/{menu:slug}', [AnjuranAnemiaController::class, 'update'])->name('update');
  });

  Route::middleware('role:admin')->prefix('anjuran-menyusui')->name('anjuran-menyusui.')->group(function () {
    Route::get('/', [AnjuranMenyusuiController::class, 'index'])->name('index');
    Route::get('/{menu:slug}', [AnjuranMenyusuiController::class, 'edit'])->name('edit');
    Route::put('/{menu:slug}', [AnjuranMenyusuiController::class, 'update'])->name('update');
  });

  Route::middleware('role:admin')->prefix('anjuran-status-gizi')->name('anjuran-status-gizi.')->group(function () {
    Route::get('/', [AnjuranStatusGiziController::class, 'index'])->name('index');
    Route::get('/{menu:slug}', [AnjuranStatusGiziController::class, 'edit'])->name('edit');
    Route::put('/{menu:slug}', [AnjuranStatusGiziController::class, 'update'])->name('update');
  });

  Route::prefix('education')->name('education.')->group(function () {
    Route::get('/', [EducationController::class, 'index'])->name('index');
    Route::get('/{menu:slug}', [EducationController::class, 'showMenu'])->name('menus.show');
    Route::put('/{menu:slug}', [EducationController::class, 'updateMenu'])
      ->name('menus.update')
      ->middleware('role:admin');
    Route::get('/{menu:slug}/{item}', [EducationController::class, 'showContent'])->name('contents.show');
    Route::put('/{menu:slug}/{item}', [EducationController::class, 'updateContent'])
      ->name('contents.update')
      ->middleware('role:admin');
  });

  Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/splash', [AppBrandingController::class, 'editSplash'])->name('splash.edit');
    Route::post('/splash', [AppBrandingController::class, 'updateSplash'])->name('splash.update');
    Route::delete('/splash', [AppBrandingController::class, 'destroySplash'])->name('splash.destroy');
    Route::get('/app-info', [AppInfoController::class, 'edit'])->name('app-info.edit');
    Route::put('/app-info', [AppInfoController::class, 'update'])->name('app-info.update');
  });

  Route::resource('users', UserController::class)->except(['show'])->middleware('role:admin');

  Route::middleware('role:admin')->prefix('statistics')->name('statistics.')->group(function () {
    Route::get('/', [StatisticsController::class, 'index'])->name('index');
  });

  Route::middleware('role:admin')->prefix('screening-submissions')->name('screening-submissions.')->group(function () {
    Route::get('/', [ScreeningSubmissionController::class, 'index'])->name('index');
    Route::get('/{screeningSubmission}', [ScreeningSubmissionController::class, 'show'])->name('show');
  });
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
