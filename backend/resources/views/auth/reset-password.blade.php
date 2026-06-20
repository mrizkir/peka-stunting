<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password - {{ config('app.name', 'PEKA Stunting') }}</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" href="{{ asset('images/logo_app_1.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo_app_1.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-base-200 min-h-screen">
        <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-5 py-10">
            <x-ui.card
                class="w-full"
                title="Reset password"
                description="Masukkan password baru untuk akun Anda."
            >
                <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <x-ui.input
                        label="Email"
                        name="email"
                        type="email"
                        value="{{ $email }}"
                        required
                        autofocus
                    />
                    @error('email')
                        <p class="text-error text-sm">{{ $message }}</p>
                    @enderror

                    <x-ui.input
                        label="Password baru"
                        name="password"
                        type="password"
                        required
                    />
                    @error('password')
                        <p class="text-error text-sm">{{ $message }}</p>
                    @enderror

                    <x-ui.input
                        label="Konfirmasi password"
                        name="password_confirmation"
                        type="password"
                        required
                    />

                    <x-ui.button type="submit" class="w-full">Simpan password baru</x-ui.button>
                </form>

                <p class="mt-4 text-center text-sm text-base-content/70">
                    <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Kembali ke login</a>
                </p>
            </x-ui.card>
        </main>
    </body>
</html>
