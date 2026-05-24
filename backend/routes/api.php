<?php

use App\Http\Controllers\Api\V1\AnemiaScreeningSubmissionController;
use App\Http\Controllers\Api\V1\AppController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChildController;
use App\Http\Controllers\Api\V1\ChildRiskAssessmentController;
use App\Http\Controllers\Api\V1\EducationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
	Route::get('/app/splash', [AppController::class, 'splash'])->name('api.v1.app.splash');

	Route::prefix('auth')->name('api.v1.auth.')->group(function () {
		Route::post('/register', [AuthController::class, 'register'])->name('register');
		Route::post('/login', [AuthController::class, 'login'])->name('login');
		Route::middleware('auth:sanctum')->group(function () {
			Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
			Route::get('/me', [AuthController::class, 'me'])->name('me');
		});
	});

	Route::prefix('education')->name('api.v1.education.')->group(function () {
		Route::get('/menus', [EducationController::class, 'menus'])->name('menus.index');
		Route::get('/menus/{menu:slug}', [EducationController::class, 'showMenu'])->name('menus.show');
		Route::get('/menus/{menu:slug}/contents/{item}', [EducationController::class, 'showContent'])->name('contents.show');
	});

	Route::middleware('auth:sanctum')->prefix('children')->name('api.v1.children.')->group(function () {
		Route::get('/', [ChildController::class, 'index'])->name('index');
		Route::post('/', [ChildController::class, 'store'])->name('store');
		Route::get('/{child}', [ChildController::class, 'show'])->name('show');
		Route::get('/{child}/measurements', [ChildController::class, 'measurements'])->name('measurements.index');
		Route::post('/{child}/measurements', [ChildController::class, 'storeMeasurement'])->name('measurements.store');
		Route::get('/{child}/risk-assessments', [ChildRiskAssessmentController::class, 'index'])->name('risk-assessments.index');
		Route::get('/{child}/risk-assessments/latest', [ChildRiskAssessmentController::class, 'latest'])->name('risk-assessments.latest');
		Route::post('/{child}/risk-assessments', [ChildRiskAssessmentController::class, 'store'])->name('risk-assessments.store');
	});

	Route::middleware('auth:sanctum')
		->prefix('screening-submissions/cek-risiko-anemia')
		->name('api.v1.screening-submissions.anemia.')
		->group(function () {
			Route::get('/', [AnemiaScreeningSubmissionController::class, 'index'])->name('index');
			Route::post('/', [AnemiaScreeningSubmissionController::class, 'store'])->name('store');
		});
});
