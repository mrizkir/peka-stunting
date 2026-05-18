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

```bash
cd mobile
flutter pub get
flutter run
```

**Emulator Android** memakai URL default `http://10.0.2.2:8000/api/v1` (host = laptop yang menjalankan `php artisan serve`).

Ubah base URL saat build/run:

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.x.x:8000/api/v1
```

## Fitur mobile MVP

- Registrasi pengguna (role `user`) + login (Sanctum token)
- Login kader (Sanctum token)
- Edukasi (baca menu & konten published)
- Data anak (daftar, tambah, detail)
- Input pengukuran
- Screening risiko stunting
