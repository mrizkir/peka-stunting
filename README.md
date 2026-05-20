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

Akun demo:
- Admin: `admin@anugerahbintan.ac.id` / `12345678`
- Kader: `kader@anugerahbintan.ac.id` / `12345678`

## Mobile (Flutter)

Panduan lengkap: **[mobile/README.md](mobile/README.md)**.

App mengarah ke API production:

**`https://peka-stunting.yacanet.com/api/v1`**

Cukup internet di HP (WiFi atau mobile data). Tidak perlu `php artisan serve` untuk menjalankan mobile.

```bash
cd mobile
flutter pub get
flutter run -d <device_id>
```

Override ke backend lokal (opsional): lihat [mobile/README.md](mobile/README.md#development-lokal-opsional).

## Fitur mobile MVP

- Registrasi pengguna (role `user`) + login (Sanctum token)
- Login kader (Sanctum token)
- Edukasi (baca menu & konten published)
- Data anak (daftar, tambah, detail)
- Input pengukuran
- Screening risiko stunting
