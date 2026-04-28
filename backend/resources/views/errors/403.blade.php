<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="bg-base-200 min-h-screen">
    <main class="mx-auto flex min-h-screen w-full max-w-xl items-center px-5 py-10">
      <div class="card bg-base-100 w-full shadow-sm">
        <div class="card-body p-8 text-center">
          <p class="text-primary text-sm font-semibold tracking-wide">Error 403</p>
          <h1 class="mt-2 text-2xl font-bold text-base-content">{{ $title ?? 'Akses ditolak' }}</h1>
          <p class="mt-3 text-sm text-base-content/70">
            {{ $message ?? 'Anda tidak memiliki izin untuk membuka halaman ini.' }}
          </p>

          <div class="mt-6 flex justify-center gap-3">
            <a href="{{ url()->previous() }}" class="btn btn-ghost">Kembali</a>
            <a href="{{ url('/') }}" class="btn btn-primary">Ke Dashboard</a>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
