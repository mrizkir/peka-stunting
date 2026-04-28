<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - {{ config('app.name', 'PEKA Stunting') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-base-200 min-h-screen">
        <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-5 py-10">
            <x-ui.card
                class="w-full"
                title="Masuk ke aplikasi"
                description="Gunakan email dan password akun Anda untuk melanjutkan."
            >
                <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
                    @csrf

                    <x-ui.input
                        label="Email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    />
                    @error('email')
                        <p class="text-error text-sm">{{ $message }}</p>
                    @enderror

                    <x-ui.input
                        label="Password"
                        name="password"
                        type="password"
                        required
                    />
                    @error('password')
                        <p class="text-error text-sm">{{ $message }}</p>
                    @enderror

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm">
                        <span>Ingat saya</span>
                    </label>

                    <x-ui.button type="submit" class="w-full">Masuk</x-ui.button>
                </form>

                <p class="mt-4 text-center text-sm text-base-content/70">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-primary font-medium hover:underline">Daftar sekarang</a>
                </p>
            </x-ui.card>
        </main>
    </body>
</html>
