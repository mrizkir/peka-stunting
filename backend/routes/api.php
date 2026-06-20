<?php

use App\Http\Controllers\Api\V1\AnemiaCalculatorEvaluateController;
use App\Http\Controllers\Api\V1\AnemiaScreeningSubmissionController;
use App\Http\Controllers\Api\V1\BreastfeedingCalculatorEvaluateController;
use App\Http\Controllers\Api\V1\BreastfeedingScreeningSubmissionController;
use App\Http\Controllers\Api\V1\BmiCalculatorEvaluateController;
use App\Http\Controllers\Api\V1\BmiScreeningSubmissionController;
use App\Http\Controllers\Api\V1\LilaCalculatorEvaluateController;
use App\Http\Controllers\Api\V1\LilaScreeningSubmissionController;
use App\Http\Controllers\Api\V1\NutritionalStatusCalculatorEvaluateController;
use App\Http\Controllers\Api\V1\NutritionalStatusScreeningSubmissionController;
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
		Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
		Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
		Route::middleware('auth:sanctum')->group(function () {
			Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
			Route::get('/me', [AuthController::class, 'me'])->name('me');
			Route::post('/profile-photo', [AuthController::class, 'updateProfilePhoto'])->name('profile-photo.update');
			Route::delete('/profile-photo', [AuthController::class, 'destroyProfilePhoto'])->name('profile-photo.destroy');
			Route::delete('/account', [AuthController::class, 'destroyAccount'])->name('account.destroy');
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

	Route::prefix('calculators/cek-risiko-anemia')
		->name('api.v1.calculators.anemia.')
		->group(function () {
			Route::post('/evaluate', [AnemiaCalculatorEvaluateController::class, 'evaluate'])->name('evaluate');
		});

	Route::middleware('auth:sanctum')
		->prefix('screening-submissions/cek-risiko-anemia')
		->name('api.v1.screening-submissions.anemia.')
		->group(function () {
			Route::get('/', [AnemiaScreeningSubmissionController::class, 'index'])->name('index');
			Route::post('/', [AnemiaScreeningSubmissionController::class, 'store'])->name('store');
		});

	Route::prefix('calculators/cek-keberhasilan-menyusui')
		->name('api.v1.calculators.menyusui.')
		->group(function () {
			Route::post('/evaluate', [BreastfeedingCalculatorEvaluateController::class, 'evaluate'])->name('evaluate');
		});

	Route::middleware('auth:sanctum')
		->prefix('screening-submissions/cek-keberhasilan-menyusui')
		->name('api.v1.screening-submissions.menyusui.')
		->group(function () {
			Route::get('/', [BreastfeedingScreeningSubmissionController::class, 'index'])->name('index');
			Route::post('/', [BreastfeedingScreeningSubmissionController::class, 'store'])->name('store');
		});

	Route::prefix('calculators/cek-imt')
		->name('api.v1.calculators.bmi.')
		->group(function () {
			Route::post('/evaluate', [BmiCalculatorEvaluateController::class, 'evaluate'])->name('evaluate');
		});

	Route::middleware('auth:sanctum')
		->prefix('screening-submissions/cek-imt')
		->name('api.v1.screening-submissions.bmi.')
		->group(function () {
			Route::post('/', [BmiScreeningSubmissionController::class, 'store'])->name('store');
		});

	Route::prefix('calculators/cek-lila')
		->name('api.v1.calculators.lila.')
		->group(function () {
			Route::post('/evaluate', [LilaCalculatorEvaluateController::class, 'evaluate'])->name('evaluate');
		});

	Route::middleware('auth:sanctum')
		->prefix('screening-submissions/cek-lila')
		->name('api.v1.screening-submissions.lila.')
		->group(function () {
			Route::post('/', [LilaScreeningSubmissionController::class, 'store'])->name('store');
		});

	Route::prefix('calculators/periksa-status-gizi')
		->name('api.v1.calculators.nutritional-status.')
		->group(function () {
			Route::post('/evaluate', [NutritionalStatusCalculatorEvaluateController::class, 'evaluate'])->name('evaluate');
		});

	Route::middleware('auth:sanctum')
		->prefix('screening-submissions/periksa-status-gizi')
		->name('api.v1.screening-submissions.nutritional-status.')
		->group(function () {
			Route::post('/', [NutritionalStatusScreeningSubmissionController::class, 'store'])->name('store');
		});
});
