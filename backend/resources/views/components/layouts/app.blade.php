<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'PEKA Stunting') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_app_1.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_app_1.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="min-h-screen text-base-content antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
      <aside class="border-base-300/80 bg-base-100/90 border-b px-6 py-5 shadow-sm backdrop-blur lg:min-h-screen lg:border-r lg:border-b-0">
        <div class="flex items-center gap-3">
          <img
            src="{{ asset('images/logo_app_1.png') }}"
            alt="Logo PEKA Stunting"
            class="h-11 w-11 shrink-0 rounded-2xl object-cover shadow-md"
          />
          <div>
            <p class="text-sm font-semibold text-base-content">PEKA Stunting</p>
            <p class="text-base-content/60 text-xs">AKBID Anugerah Bintan</p>
          </div>
        </div>

        <nav class="mt-8 space-y-2">
          <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'nav-link-active' : 'nav-link' }}">
            Dashboard
          </a>
          <a href="{{ route('education.index') }}" class="{{ request()->routeIs('education.*') ? 'nav-link-active' : 'nav-link' }}">
            Menu edukasi
          </a>
          @role('admin')
            <a href="{{ route('anjuran-imt.index') }}" class="{{ request()->routeIs('anjuran-imt.*') ? 'nav-link-active' : 'nav-link' }}">
              Anjuran IMT
            </a>
            <a href="{{ route('anjuran-lila.index') }}" class="{{ request()->routeIs('anjuran-lila.*') ? 'nav-link-active' : 'nav-link' }}">
              Anjuran LILA
            </a>
            <a href="{{ route('anjuran-anemia.index') }}" class="{{ request()->routeIs('anjuran-anemia.*') ? 'nav-link-active' : 'nav-link' }}">
              Anjuran Anemia
            </a>
            <a href="{{ route('anjuran-menyusui.index') }}" class="{{ request()->routeIs('anjuran-menyusui.*') ? 'nav-link-active' : 'nav-link' }}">
              Anjuran Menyusui
            </a>
            <a href="{{ route('anjuran-status-gizi.index') }}" class="{{ request()->routeIs('anjuran-status-gizi.*') ? 'nav-link-active' : 'nav-link' }}">
              Anjuran Status Gizi
            </a>
            <a href="{{ route('settings.splash.edit') }}" class="{{ request()->routeIs('settings.splash.*') ? 'nav-link-active' : 'nav-link' }}">
              Splash screen
            </a>
            <a href="{{ route('settings.app-info.edit') }}" class="{{ request()->routeIs('settings.app-info.*') ? 'nav-link-active' : 'nav-link' }}">
              Info aplikasi
            </a>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'nav-link-active' : 'nav-link' }}">
              Kelola user
            </a>
            <a href="{{ route('screening-submissions.index') }}" class="{{ request()->routeIs('screening-submissions.*') ? 'nav-link-active' : 'nav-link' }}">
              Jawaban skrining
            </a>
          @endrole
        </nav>

        <form action="{{ route('logout') }}" method="POST" class="mt-6">
          @csrf
          <button type="submit" class="nav-link w-full text-left">
            Logout
          </button>
        </form>
      </aside>

      <div class="min-w-0">
        <header class="border-base-300/70 bg-base-100/75 border-b px-6 py-4 backdrop-blur">
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              @isset($eyebrow)
                <p class="text-primary text-sm font-medium">{{ $eyebrow }}</p>
              @endisset
              <h1 class="{{ isset($eyebrow) ? 'mt-1' : '' }} text-2xl font-semibold tracking-tight text-base-content">{{ $heading ?? 'PEKA Stunting' }}</h1>
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
