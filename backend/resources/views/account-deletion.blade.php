<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Hapus akun - {{ config('app.name', 'PEKA Stunting') }}</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" href="{{ asset('images/logo_app_1.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo_app_1.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-base-200 min-h-screen">
        <main class="mx-auto w-full max-w-3xl px-5 py-10">
            <x-ui.card
                title="Permintaan penghapusan akun PEKA Stunting"
                description="Halaman ini untuk pengguna yang ingin menghapus akun dan data terkait dari aplikasi PEKA Stunting."
            >
                <div class="prose prose-sm max-w-none space-y-6 text-base-content leading-7">
                    <p>
                        Anda dapat meminta penghapusan akun kader/pengguna beserta data skrining
                        yang terkait dengan akun tersebut. Tindakan ini permanen dan tidak dapat dibatalkan.
                    </p>

                    <section class="space-y-2">
                        <h3 class="text-base font-semibold">1. Hapus langsung di aplikasi (disarankan)</h3>
                        <ol class="list-decimal space-y-1 pl-5">
                            <li>Buka aplikasi <strong>PEKA Stunting</strong> di Android.</li>
                            <li>Masuk dengan email dan password akun Anda.</li>
                            <li>Buka tab <strong>Profil</strong>.</li>
                            <li>Di bagian <strong>Zona berbahaya</strong>, ketuk <strong>Hapus akun</strong>.</li>
                            <li>Konfirmasi <strong>Hapus permanen</strong>.</li>
                        </ol>
                        <p>
                            Setelah berhasil, akun dihapus dan Anda dikembalikan ke layar login.
                            Tombol hapus akun hanya tersedia untuk peran <strong>kader</strong> dan <strong>user</strong>.
                            Akun <strong>admin</strong> tidak dapat dihapus mandiri dari aplikasi.
                        </p>
                    </section>

                    <section class="space-y-2">
                        <h3 class="text-base font-semibold">2. Jika aplikasi sudah tidak terpasang</h3>
                        <p>
                            Kirim email ke
                            <a href="mailto:nining@anugerahbintan.ac.id?subject=Hapus%20akun%20PEKA%20Stunting" class="text-primary font-medium hover:underline">nining@anugerahbintan.ac.id</a>
                            dengan subjek <strong>Hapus akun PEKA Stunting</strong>.
                        </p>
                        <p>Sertakan:</p>
                        <ul class="list-disc space-y-1 pl-5">
                            <li>Nama lengkap sesuai akun</li>
                            <li>Alamat email akun</li>
                            <li>Nomor HP yang terdaftar (jika ada)</li>
                            <li>Permintaan eksplisit untuk menghapus akun dan data terkait</li>
                        </ul>
                        <p>
                            Kami akan memverifikasi kepemilikan akun sebelum menghapus data.
                        </p>
                    </section>

                    <section class="space-y-2">
                        <h3 class="text-base font-semibold">3. Data yang dihapus</h3>
                        <p>
                            Penghapusan akun mengakhiri akses login dan menghapus data skrining
                            yang terkait dengan akun tersebut, sesuai alur di aplikasi.
                        </p>
                    </section>

                    <p class="text-sm text-base-content/70">
                        Lihat juga
                        <a href="{{ route('privacy') }}" class="text-primary font-medium hover:underline">Kebijakan privasi</a>.
                    </p>
                </div>
            </x-ui.card>
        </main>
    </body>
</html>
