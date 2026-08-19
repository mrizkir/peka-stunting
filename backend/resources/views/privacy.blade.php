<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pekahealth">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Kebijakan Privasi - {{ config('app.name', 'PEKA Stunting') }}</title>

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
				title="Kebijakan Privasi PEKA Stunting"
				description="Terakhir diperbarui: 19 Agustus 2026"
			>
				<div class="prose prose-sm max-w-none space-y-6 text-base-content leading-7">
					<p>
						Kebijakan ini menjelaskan bagaimana aplikasi Android <strong>PEKA Stunting</strong>
						(package <code>id.ac.anugerahbintan.pekastunting</code>) mengumpulkan, menggunakan,
						dan menyimpan data. Aplikasi ini dikelola oleh
						<strong>AKBID Anugerah Bintan / Yayasan Anugerah Bintan</strong> untuk mendukung
						edukasi, pendataan anak, dan skrining terkait pencegahan stunting oleh kader.
					</p>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">1. Pengelola data</h3>
						<p>
							Data diproses dan disimpan di server kami:
							<a href="https://peka-stunting.anugerahbintan.ac.id" class="text-primary font-medium hover:underline">https://peka-stunting.anugerahbintan.ac.id</a>.
						</p>
						<p>
							Kontak: <a href="mailto:nining@anugerahbintan.ac.id" class="text-primary font-medium hover:underline">nining@anugerahbintan.ac.id</a>
						</p>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">2. Data yang kami kumpulkan</h3>
						<p>Data hanya dikumpulkan jika Anda menggunakan fitur terkait.</p>
						<ul class="list-disc space-y-1 pl-5">
							<li>
								<strong>Akun kader/pengguna:</strong> nama, email, nomor HP, jenis kelamin,
								tanggal lahir, password (disimpan terenkripsi), dan foto profil (opsional).
							</li>
							<li>
								<strong>Data anak:</strong> nama, jenis kelamin, tanggal lahir, NIK (opsional),
								desa, posyandu, dan catatan.
							</li>
							<li>
								<strong>Data wali:</strong> nama, nomor HP, hubungan dengan anak, dan alamat.
							</li>
							<li>
								<strong>Pengukuran tumbuh kembang:</strong> tanggal ukur, berat badan, tinggi badan,
								umur dalam bulan, dan catatan.
							</li>
							<li>
								<strong>Skrining kesehatan:</strong> hasil cek risiko anemia, keberhasilan menyusui,
								IMT, LILA, status gizi, dan asesmen risiko stunting beserta anjuran yang diberikan.
							</li>
							<li>
								<strong>Penggunaan aplikasi:</strong> event dan sesi pemakaian (misalnya halaman yang dibuka),
								versi aplikasi, dan platform (Android), untuk statistik internal dan Google Firebase Analytics.
							</li>
						</ul>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">3. Izin perangkat</h3>
						<p>
							Aplikasi dapat meminta akses <strong>internet</strong> (wajib untuk login dan sinkronisasi data)
							serta <strong>kamera / galeri</strong> jika Anda mengunggah foto profil. Kami tidak mengakses
							kamera atau galeri tanpa tindakan Anda.
						</p>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">4. Tujuan penggunaan</h3>
						<ul class="list-disc space-y-1 pl-5">
							<li>Membuat dan mengelola akun kader.</li>
							<li>Menyimpan pendataan anak, pengukuran, dan hasil skrining.</li>
							<li>Menampilkan konten edukasi stunting yang diterbitkan admin.</li>
							<li>Memberi anjuran sesuai hasil kalkulator/skrining.</li>
							<li>Membantu admin memantau statistik pemakaian dan hasil skrining.</li>
							<li>Memperbaiki kualitas aplikasi (analytics).</li>
						</ul>
						<p>Kami tidak menjual data pengguna kepada pihak ketiga untuk iklan.</p>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">5. Siapa yang dapat mengakses data</h3>
						<ul class="list-disc space-y-1 pl-5">
							<li><strong>Kader/pengguna:</strong> data akun sendiri serta data anak dan skrining yang diinput melalui aplikasinya.</li>
							<li><strong>Admin:</strong> pengelolaan akun, konten edukasi, anjuran, dan rekap skrining/statistik di CMS.</li>
						</ul>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">6. Pihak ketiga</h3>
						<p>
							Aplikasi menggunakan <strong>Google Firebase Analytics</strong> (project Firebase
							<code>peka-stunting</code>) untuk mengukur pemakaian aplikasi. Google dapat memproses
							data analytics sesuai kebijakan privasi Google.
						</p>
						<p>
							Distribusi lewat Google Play mengikuti kebijakan Google Play, termasuk penandatanganan
							aplikasi oleh Google Play App Signing.
						</p>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">7. Penyimpanan dan keamanan</h3>
						<p>
							Data disimpan di server PEKA Stunting. Password tidak disimpan dalam bentuk teks biasa.
							Akses API memakai autentikasi (token). Meskipun demikian, tidak ada sistem yang 100%
							bebas risiko; kami berupaya melindungi data sesuai kemampuan yang wajar.
						</p>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">8. Hak Anda</h3>
						<ul class="list-disc space-y-1 pl-5">
							<li>Memperbarui data akun dan foto profil di aplikasi.</li>
							<li>
								Menghapus akun kader/pengguna dari aplikasi (akun admin tidak dapat dihapus mandiri).
								Penghapusan akun mengakhiri akses login tersebut.
								Panduan lengkap:
								<a href="{{ route('account-deletion') }}" class="text-primary font-medium hover:underline">hapus akun dan data terkait</a>.
							</li>
							<li>
								Menghubungi kami di
								<a href="mailto:nining@anugerahbintan.ac.id" class="text-primary font-medium hover:underline">nining@anugerahbintan.ac.id</a>
								untuk pertanyaan, koreksi, atau permintaan terkait data.
							</li>
						</ul>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">9. Data anak</h3>
						<p>
							Data anak diinput oleh kader untuk keperluan pendampingan gizi dan pencegahan stunting.
							Data ini termasuk data kesehatan/identitas yang sensitif dan hanya digunakan untuk tujuan
							tersebut, bukan untuk iklan.
						</p>
					</section>

					<section class="space-y-2">
						<h3 class="text-base font-semibold">10. Perubahan kebijakan</h3>
						<p>
							Kebijakan ini dapat diperbarui. Tanggal “terakhir diperbarui” di atas akan disesuaikan
							jika ada perubahan material. Penggunaan aplikasi setelah pembaruan berarti Anda memahami
							kebijakan yang berlaku.
						</p>
					</section>
				</div>
			</x-ui.card>
		</main>
	</body>
</html>
