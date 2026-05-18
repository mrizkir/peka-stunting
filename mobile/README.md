# PEKA Stunting — Mobile

Aplikasi Android (Flutter) untuk kader.

## Stack

- Flutter 3
- Riverpod
- Dio
- go_router
- shared_preferences

## Menjalankan

Pastikan backend Laravel berjalan (`php artisan serve` di folder `backend`).

```bash
flutter pub get
flutter run
```

## Konfigurasi API

| Lingkungan | URL default |
|------------|-------------|
| Emulator Android | `http://10.0.2.2:8000/api/v1` |
| Device fisik (LAN) | `http://<IP-laptop>:8000/api/v1` |

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000/api/v1
```

## Akun

**Registrasi:** layar login → *Belum punya akun? Daftar* (role `user`, otomatis login setelah daftar).

**Login kader (demo):**

- Email: `kader@anugerahbintan.ac.id`
- Password: `12345678`

Pastikan sudah menjalankan `php artisan db:seed` di backend.
