<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - {{ config('app.name', 'PEKA Stunting') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="bg-base-200 min-h-screen">
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-5 py-10">
      <x-ui.card
        class="w-full"
        title="Daftar akun baru"
        description="Setiap akun baru akan otomatis memiliki role user."
      >
        <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
          @csrf

          <x-ui.input
            label="Nama"
            name="name"
            type="text"
            value="{{ old('name') }}"
            required
            autofocus
          />
          @error('name')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="Email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            required
          />
          @error('email')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="No. HP"
            name="phone"
            type="text"
            value="{{ old('phone') }}"
            maxlength="15"
            required
          />
          @error('phone')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-base-content/80">Jenis Kelamin</span>
            <div class="relative">
              <select
                name="gender"
                class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full appearance-none rounded-md border px-4 py-3 pr-12 text-sm shadow-sm outline-none transition focus:ring-4"
              >
                <option value="">Pilih jenis kelamin</option>
                <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
              </select>
              <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-base-content/70">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.118l3.71-3.89a.75.75 0 111.08 1.04l-4.25 4.455a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
              </span>
            </div>
          </label>
          @error('gender')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="Tanggal Lahir"
            name="birth_date"
            type="date"
            value="{{ old('birth_date') }}"
          />
          @error('birth_date')
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

          <x-ui.input
            label="Konfirmasi Password"
            name="password_confirmation"
            type="password"
            required
          />

          <x-ui.button type="submit" class="w-full">Daftar</x-ui.button>
        </form>

        <p class="mt-4 text-center text-sm text-base-content/70">
          Sudah punya akun?
          <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Masuk di sini</a>
        </p>
      </x-ui.card>
    </main>
  </body>
</html>
