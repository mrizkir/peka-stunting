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

Panduan lengkap (HP fisik, emulator, `adb reverse`, troubleshooting): **[mobile/README.md](mobile/README.md)**.

### Ringkas

**Backend** (terminal 1):

```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000   # HP di WiFi yang sama
# atau
php artisan serve --host=127.0.0.1 --port=8000   # jika pakai adb reverse (USB)
```

**App** (terminal 2):

```bash
cd mobile
flutter pub get
flutter run -d <device_id>
```

| Skenario | Perintah run |
|----------|----------------|
| Emulator | `flutter run` (default `http://10.0.2.2:8000/api/v1`) |
| HP fisik, **WiFi sama** dengan laptop | `flutter run -d <id> --dart-define=API_BASE_URL=http://<IP-laptop>:8000/api/v1` |
| HP **mobile data**, laptop WiFi (beda jaringan) | USB + `adb reverse`, lalu run dengan `127.0.0.1` (lihat bawah) |

### HP mobile data + laptop WiFi (beda jaringan)

IP LAN (`192.168.x.x`) tidak bisa diakses dari mobile data. Colok HP via USB, lalu:

```bash
adb reverse tcp:8000 tcp:8000
```

Backend: `php artisan serve --host=127.0.0.1 --port=8000`

```bash
cd mobile
flutter run -d <device_id> \
  --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
```

Ulangi `adb reverse` setelah cabut USB atau restart HP.

## Fitur mobile MVP

- Registrasi pengguna (role `user`) + login (Sanctum token)
- Login kader (Sanctum token)
- Edukasi (baca menu & konten published)
- Data anak (daftar, tambah, detail)
- Input pengukuran
- Screening risiko stunting
