<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
  public function showLogin(): View
  {
    return view('auth.login');
  }

  public function login(Request $request): RedirectResponse
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) 
    {
      return back()
        ->withErrors(['email' => 'Email atau password tidak sesuai.'])
        ->onlyInput('email');
    }

    $request->session()->regenerate();

    return redirect()->intended('/');
  }

  public function showRegister(): View
  {
    return view('auth.register');
  }

  public function register(RegisterRequest $request): RedirectResponse
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
    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->intended('/');
  }

  public function logout(Request $request): RedirectResponse
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
  }
}
