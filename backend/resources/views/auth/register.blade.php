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
        <form
          action="{{ route('register.store') }}"
          method="POST"
          class="space-y-4"
          x-ref="registerForm"
          x-data="window.registerForm(
            {
              name: @js(old('name', '')),
              email: @js(old('email', '')),
              phone: @js(old('phone', '')),
              gender: @js(old('gender', '')),
              birth_date: @js(old('birth_date', '')),
              password: '',
              password_confirmation: '',
            },
            {
              name: @js($errors->first('name')),
              email: @js($errors->first('email')),
              phone: @js($errors->first('phone')),
              gender: @js($errors->first('gender')),
              birth_date: @js($errors->first('birth_date')),
              password: @js($errors->first('password')),
              password_confirmation: @js($errors->first('password_confirmation')),
            }
          )"
          novalidate
        >
          @csrf

          <x-ui.input
            label="Nama"
            name="name"
            type="text"
            value="{{ old('name') }}"
            required
            autofocus
            x-model="form.name"
            @blur="validateField('name')"
            x-bind:aria-invalid="errors.name ? 'true' : 'false'"
            x-bind:class="errors.name ? 'border-error focus:border-error focus:ring-error/20' : ''"
          />
          <p x-show="errors.name" x-text="errors.name" class="text-error text-sm"></p>
          @error('name')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="Email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            required
            x-model="form.email"
            @blur="validateField('email')"
            x-bind:aria-invalid="errors.email ? 'true' : 'false'"
            x-bind:class="errors.email ? 'border-error focus:border-error focus:ring-error/20' : ''"
          />
          <p x-show="errors.email" x-text="errors.email" class="text-error text-sm"></p>
          @error('email')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="No. HP"
            name="phone"
            type="text"
            value="{{ old('phone') }}"
            maxlength="15"
            inputmode="numeric"
            pattern="[0-9]*"
            autocomplete="tel"
            required
            x-model="form.phone"
            @blur="validateField('phone')"
            @input="form.phone = $event.target.value.replace(/\D/g, '').slice(0, 15); $event.target.value = form.phone; validateField('phone')"
            x-bind:aria-invalid="errors.phone ? 'true' : 'false'"
            x-bind:class="errors.phone ? 'border-error focus:border-error focus:ring-error/20' : ''"
          />
          <p x-show="errors.phone" x-text="errors.phone" class="text-error text-sm"></p>
          @error('phone')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-base-content/80">Jenis Kelamin</span>
            <div class="relative">
              <select
                name="gender"
                x-model="form.gender"
                @change="validateField('gender')"
                @blur="validateField('gender')"
                :aria-invalid="errors.gender ? 'true' : 'false'"
                :class="errors.gender ? 'border-error focus:border-error focus:ring-error/20' : ''"
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
          <p x-show="errors.gender" x-text="errors.gender" class="text-error text-sm"></p>
          @error('gender')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="Tanggal Lahir"
            name="birth_date"
            type="date"
            value="{{ old('birth_date') }}"
            x-model="form.birth_date"
            @blur="validateField('birth_date')"
            @change="validateField('birth_date')"
            x-bind:aria-invalid="errors.birth_date ? 'true' : 'false'"
            x-bind:class="errors.birth_date ? 'border-error focus:border-error focus:ring-error/20' : ''"
          />
          <p x-show="errors.birth_date" x-text="errors.birth_date" class="text-error text-sm"></p>
          @error('birth_date')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="Password"
            name="password"
            type="password"
            required
            x-model="form.password"
            @blur="validateField('password')"
            x-bind:aria-invalid="errors.password ? 'true' : 'false'"
            x-bind:class="errors.password ? 'border-error focus:border-error focus:ring-error/20' : ''"
          />
          <p x-show="errors.password" x-text="errors.password" class="text-error text-sm"></p>
          @error('password')
            <p class="text-error text-sm">{{ $message }}</p>
          @enderror

          <x-ui.input
            label="Konfirmasi Password"
            name="password_confirmation"
            type="password"
            required
            x-model="form.password_confirmation"
            @blur="validateField('password_confirmation')"
            x-bind:aria-invalid="errors.password_confirmation ? 'true' : 'false'"
            x-bind:class="errors.password_confirmation ? 'border-error focus:border-error focus:ring-error/20' : ''"
          />
          <p x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="text-error text-sm"></p>

          <x-ui.button type="button" class="w-full" @click.prevent="submitForm()">Daftar</x-ui.button>
        </form>

        <p class="mt-4 text-center text-sm text-base-content/70">
          Sudah punya akun?
          <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Masuk di sini</a>
        </p>
      </x-ui.card>
    </main>
  </body>
</html>
