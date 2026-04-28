<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'PEKA Stunting') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="min-h-screen text-base-content antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
      <aside class="border-base-300/80 bg-base-100/90 border-b px-6 py-5 shadow-sm backdrop-blur lg:min-h-screen lg:border-r lg:border-b-0">
        <div class="flex items-center gap-3">
          <div class="bg-primary text-primary-content flex h-11 w-11 items-center justify-center rounded-2xl text-lg font-bold shadow-md">
            P
          </div>
          <div>
            <p class="text-sm font-semibold text-base-content">PEKA Stunting</p>
            <p class="text-base-content/60 text-xs">AKBID Anugerah Bintan</p>
          </div>
        </div>

        <nav class="mt-8 space-y-2">
          <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'nav-link-active' : 'nav-link' }}">
            Dashboard
          </a>
          <a href="{{ route('education.index') }}" class="{{ request()->routeIs('education.index') ? 'nav-link-active' : 'nav-link' }}">
            Menu edukasi
          </a>
          <a href="{{ route('education.show') }}" class="{{ request()->routeIs('education.show') ? 'nav-link-active' : 'nav-link' }}">
            Detail konten
          </a>
          <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'nav-link-active' : 'nav-link' }}">
            Kelola user
          </a>
        </nav>

        <div class="from-primary to-secondary text-primary-content mt-8 rounded-3xl bg-gradient-to-br p-5 shadow-lg">
          <p class="text-sm font-semibold">Sprint 1 focus</p>
          <p class="text-primary-content/85 mt-2 text-sm leading-6">
            Setup base layout Tailwind, komponen reusable, dan template halaman edukasi yang siap dihubungkan ke data backend.
          </p>
        </div>
      </aside>

      <div class="min-w-0">
        <header class="border-base-300/70 bg-base-100/75 border-b px-6 py-4 backdrop-blur">
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <p class="text-primary text-sm font-medium">{{ $eyebrow ?? 'Template admin' }}</p>
              <h1 class="mt-1 text-2xl font-semibold tracking-tight text-base-content">{{ $heading ?? 'PEKA Stunting' }}</h1>
              @isset($description)
                <p class="text-base-content/65 mt-2 max-w-3xl text-sm leading-6">{{ $description }}</p>
              @endisset
            </div>

            @isset($headerActions)
              <div class="flex items-center gap-3">
                {{ $headerActions }}
              </div>
            @endisset
          </div>
        </header>

        <main class="p-6">
          {{ $slot }}
        </main>
      </div>
    </div>
  </body>
</html>
