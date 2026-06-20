<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\UploadProfilePhotoRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\UserProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
	public function __construct(
		private readonly UserProfileService $userProfile,
	) {}

	public function register(RegisterRequest $request): JsonResponse
	{
		$validated = $request->validated();

		$user = User::create([
			'name' => $validated['name'],
			'email' => $validated['email'],
			'password' => Hash::make($validated['password']),
			'phone' => $validated['phone'],
			'gender' => $validated['gender'] ?? null,
			'birth_date' => $validated['birth_date'] ?? null,
		]);

		$user->assignRole('user');

		$deviceName = $validated['device_name'] ?? 'mobile';
		$token = $user->createToken($deviceName)->plainTextToken;

		return ApiResponse::success([
			'token' => $token,
			'token_type' => 'Bearer',
			'user' => (new UserResource($user))->resolve($request),
		], 201);
	}

	public function login(LoginRequest $request): JsonResponse
	{
		$user = User::query()
			->where('email', $request->validated('email'))
			->first();

		if ($user === null || ! Hash::check($request->validated('password'), $user->password)) {
			throw ValidationException::withMessages([
				'email' => ['Email atau password tidak sesuai.'],
			]);
		}

		$deviceName = $request->validated('device_name') ?? 'mobile';
		$token = $user->createToken($deviceName)->plainTextToken;

		return ApiResponse::success([
			'token' => $token,
			'token_type' => 'Bearer',
			'user' => (new UserResource($user))->resolve($request),
		]);
	}

	public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
	{
		Password::sendResetLink($request->validated());

		return ApiResponse::success([
			'message' => 'Jika email terdaftar, kami mengirim instruksi reset password.',
		]);
	}

	public function resetPassword(ResetPasswordRequest $request): JsonResponse
	{
		$status = Password::reset(
			$request->validated(),
			function (User $user, string $password) {
				$user->forceFill([
					'password' => Hash::make($password),
					'remember_token' => Str::random(60),
				])->save();

				$user->tokens()->delete();

				event(new PasswordReset($user));
			},
		);

		if ($status !== Password::PASSWORD_RESET) {
			throw ValidationException::withMessages([
				'email' => [__($status)],
			]);
		}

		return ApiResponse::success([
			'message' => 'Password berhasil diubah. Silakan login.',
		]);
	}

	public function logout(Request $request): JsonResponse
	{
		$request->user()?->currentAccessToken()?->delete();

		return ApiResponse::success([
			'message' => 'Logout berhasil.',
		]);
	}

	public function me(Request $request): JsonResponse
	{
		return ApiResponse::success((new UserResource($request->user()))->resolve($request));
	}

	public function updateProfilePhoto(UploadProfilePhotoRequest $request): JsonResponse
	{
		$user = $request->user();
		$this->userProfile->updateProfilePhoto($user, $request->file('profile_photo'));

		return ApiResponse::success((new UserResource($user->fresh()))->resolve($request));
	}

	public function destroyProfilePhoto(Request $request): JsonResponse
	{
		$user = $request->user();
		$this->userProfile->deleteProfilePhoto($user);

		return ApiResponse::success((new UserResource($user->fresh()))->resolve($request));
	}

	public function destroyAccount(Request $request): JsonResponse
	{
		$user = $request->user();

		if ($user->hasRole('admin')) {
			return ApiResponse::error('Akun admin tidak dapat dihapus dari aplikasi.', 403);
		}

		if (! $user->hasAnyRole(['kader', 'user'])) {
			return ApiResponse::error('Role akun tidak diizinkan untuk penghapusan mandiri.', 403);
		}

		$user->tokens()->delete();
		$user->delete();

		return ApiResponse::success([
			'message' => 'Akun berhasil dihapus.',
		]);
	}
}
