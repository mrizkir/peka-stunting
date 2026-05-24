# PEKA Stunting

Aplikasi PEKA Stunting — backend admin (Laravel) dan aplikasi mobile kader (Flutter/Android).

## Struktur repo

```
peka-stunting/
├── backend/     # Laravel API + admin web
└── mobile/      # Flutter app (Android)
```

## Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Admin web: http://127.0.0.1:8000  
API: http://127.0.0.1:8000/api/v1

### Menjalankan test (PHPUnit + MySQL)

Test memakai database MySQL terpisah `pekastunting_testing` (bukan SQLite).

```bash
mysql -h 127.0.0.1 -P 3307 -u root -e "CREATE DATABASE IF NOT EXISTS pekastunting_testing;"
cp .env.testing.example .env.testing
# Salin APP_KEY dan DB_PASSWORD dari .env ke .env.testing jika perlu
php artisan test
```

Akun demo:
- Admin: `admin@anugerahbintan.ac.id` / `12345678`
- Kader: `kader@anugerahbintan.ac.id` / `12345678`

### Deploy production (gambar `/storage/...`)

Document root harus mengarah ke folder `backend/public`.

Setelah deploy, jalankan di server:

```bash
cd backend
php artisan storage:link
```

Ini membuat symlink `public/storage` → `storage/app/public` (upload Spatie Media Library).

Jika gambar masih 403/404, pastikan `APP_URL` di `.env` sesuai domain (`https://peka-stunting.yacanet.com`) lalu `php artisan config:clear`.

## Mobile (Flutter)

Panduan lengkap: **[mobile/README.md](mobile/README.md)**.

App mengarah ke API production:

**`https://peka-stunting.yacanet.com/api/v1`**

Cukup internet di HP (WiFi atau mobile data). Tidak perlu `php artisan serve` untuk menjalankan mobile.

```bash
cd mobile
flutter pub get
flutter devices          # daftar HP/emulator
flutter run -d <device_id>
```

### ADB (HP Android via USB)

Perintah yang benar untuk melihat HP terhubung (**bukan** `adb list`):

```bash
adb devices
```

| Status | Artinya |
|--------|---------|
| `device` | Siap dipakai |
| `unauthorized` | Terima popup *Allow USB debugging* di HP |
| (kosong) | Belum terdeteksi — cek kabel data & USB debugging |

Perintah lain:

```bash
adb kill-server && adb start-server    # restart ADB
adb reverse tcp:8000 tcp:8000          # dev lokal: HP akses backend di laptop
flutter devices                        # sama untuk Flutter
```

Detail troubleshooting USB: [mobile/README.md](mobile/README.md#hp-tidak-muncul-di-flutter-devices).

Override ke backend lokal (opsional): lihat [mobile/README.md](mobile/README.md#development-lokal-opsional).

## Fitur mobile MVP

- Registrasi pengguna (role `user`) + login (Sanctum token)
- Login kader (Sanctum token)
- Edukasi (baca menu & konten published)
- Data anak (daftar, tambah, detail)
- Input pengukuran
- Screening risiko stunting
