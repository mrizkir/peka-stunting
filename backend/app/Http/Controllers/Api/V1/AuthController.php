<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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
}
