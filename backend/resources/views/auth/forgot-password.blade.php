<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Lupa Password - {{ config('app.name', 'PEKA Stunting') }}</title>

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
                title="Lupa password"
                description="Masukkan email akun Anda. Kami akan mengirim instruksi reset password jika email terdaftar."
            >
                @if (session('status'))
                    <div class="alert alert-success mb-4 text-sm">{{ session('status') }}</div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
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

                    <x-ui.button type="submit" class="w-full">Kirim instruksi reset</x-ui.button>
                </form>

                <p class="mt-4 text-center text-sm text-base-content/70">
                    Ingat password?
                    <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Kembali ke login</a>
                </p>
            </x-ui.card>
        </main>
    </body>
</html>
