<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
	public function index(Request $request): View|JsonResponse
	{
		$perPage = (int) $request->integer('per_page', 10);
		$perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
		$search = trim((string) $request->string('q', ''));

		$users = User::query()
			->when($search !== '', function ($query) use ($search) {
				$query->where(function ($subQuery) use ($search) {
					$subQuery
						->where('name', 'like', "%{$search}%")
						->orWhere('email', 'like', "%{$search}%")
						->orWhere('phone', 'like', "%{$search}%");
				});
			})
			->latest()
			->paginate($perPage)
			->withQueryString();

		if ($request->expectsJson() || $request->boolean('ajax')) {
			return response()->json([
				'data' => $users->getCollection()->map(function (User $user) {
					return [
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'phone' => $user->phone,
						'gender_label' => $user->gender === 'L' ? 'Laki-laki' : ($user->gender === 'P' ? 'Perempuan' : '-'),
						'birth_date_label' => $user->birth_date?->format('d M Y') ?? '-',
						'edit_url' => route('users.edit', $user),
						'destroy_url' => route('users.destroy', $user),
						'is_current_auth' => auth()->id() === $user->id,
					];
				})->values()->all(),
				'current_page' => $users->currentPage(),
				'last_page' => $users->lastPage(),
				'per_page' => $users->perPage(),
				'total' => $users->total(),
				'from' => $users->firstItem(),
				'to' => $users->lastItem(),
				'prev_page_url' => $users->previousPageUrl(),
				'next_page_url' => $users->nextPageUrl(),
			]);
		}

		return view('users.index', compact('users'));
	}

	public function create(): View
	{
		return view('users.create');
	}

	public function store(StoreUserRequest $request): RedirectResponse
	{
		User::create($request->validated());

		return redirect()
			->route('users.index')
			->with('success', 'User berhasil ditambahkan.');
	}

	public function edit(User $user): View
	{
		return view('users.edit', compact('user'));
	}

	public function update(UpdateUserRequest $request, User $user): RedirectResponse
	{
		$validated = $request->validated();

		if (blank($validated['password'] ?? null)) {
			unset($validated['password']);
		}

		unset($validated['password_confirmation']);

		$user->update($validated);

		return redirect()
			->route('users.index')
			->with('success', 'User berhasil diperbarui.');
	}

	public function destroy(User $user): RedirectResponse|JsonResponse
	{
		if (auth()->id() === $user->id) {
			if (request()->expectsJson()) {
				return response()->json([
					'message' => 'Akun yang sedang digunakan tidak bisa dihapus.',
				], 422);
			}

			return back()->with('error', 'Akun yang sedang digunakan tidak bisa dihapus.');
		}

		$user->delete();

		if (request()->expectsJson()) {
			return response()->json([
				'message' => 'User berhasil dihapus.',
			]);
		}

		return redirect()
			->route('users.index')
			->with('success', 'User berhasil dihapus.');
	}
}
