# PEKA Stunting — Mobile

Aplikasi Android (Flutter) untuk kader.

## Stack

- Flutter 3
- Riverpod
- Dio
- go_router
- shared_preferences

## Prasyarat

1. Flutter SDK terpasang (`flutter doctor` — Android toolchain hijau cukup; Xcode tidak wajib untuk Android saja).
2. Backend Laravel sudah di-setup (`migrate --seed`). Lihat [README utama](../README.md#backend).
3. HP Android (opsional): USB debugging aktif, kabel data, `adb devices` menampilkan status `device`.

## Menjalankan backend

Di terminal **pertama** (folder `backend`):

```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

`--host=0.0.0.0` diperlukan agar HP di **WiFi yang sama** bisa mengakses API.  
Jika memakai **`adb reverse`** (lihat bawah), cukup:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

API: `http://127.0.0.1:8000/api/v1`

## Menjalankan aplikasi (Flutter)

Di terminal **kedua** (folder `mobile`):

```bash
cd mobile
flutter pub get
flutter devices          # pastikan HP/emulator terdeteksi
flutter run -d <device_id>
```

Ganti `<device_id>` dengan ID dari `flutter devices` (mis. `RR8N608YY2F`).

### Emulator Android

URL API default sudah benar (`10.0.2.2` = localhost laptop dari dalam emulator):

```bash
flutter run
```

Tidak perlu `--dart-define` jika backend di `http://127.0.0.1:8000`.

### HP fisik — WiFi sama dengan laptop

Laptop dan HP **harus satu jaringan WiFi**. Mobile data saja **tidak** bisa mengakses IP lokal laptop (`192.168.x.x`).

1. Cek IP laptop:

   ```bash
   ipconfig getifaddr en0    # macOS WiFi
   ```

2. Tes dari browser HP: `http://<IP-laptop>:8000` harus terbuka.

3. Jalankan app:

   ```bash
   flutter run -d <device_id> \
     --dart-define=API_BASE_URL=http://<IP-laptop>:8000/api/v1
   ```

   Contoh:

   ```bash
   flutter run -d RR8N608YY2F \
     --dart-define=API_BASE_URL=http://192.168.100.5:8000/api/v1
   ```

### HP fisik — beda jaringan (laptop WiFi, HP mobile data)

IP LAN (`192.168.x.x`) **tidak** bisa dipakai. Gunakan **USB + port forwarding**:

1. Colok HP via USB, pastikan `adb devices` → `device`.

2. Forward port backend ke HP:

   ```bash
   adb reverse tcp:8000 tcp:8000
   ```

3. Backend di laptop (boleh `127.0.0.1`):

   ```bash
   cd backend
   php artisan serve --host=127.0.0.1 --port=8000
   ```

4. Jalankan Flutter — dari sisi HP, `127.0.0.1` mengarah ke laptop lewat USB:

   ```bash
   flutter run -d <device_id> \
     --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
   ```

Setiap cabut USB atau restart HP, jalankan ulang `adb reverse tcp:8000 tcp:8000`.

## Konfigurasi API (ringkas)

| Skenario | `API_BASE_URL` |
|----------|----------------|
| Emulator Android | default `http://10.0.2.2:8000/api/v1` |
| HP fisik, WiFi sama | `http://<IP-laptop>:8000/api/v1` |
| HP fisik, USB + `adb reverse` | `http://127.0.0.1:8000/api/v1` |

Nilai default didefinisikan di `lib/core/config/app_config.dart`.  
`--dart-define` hanya berlaku saat **build/run**; ubah URL → stop lalu `flutter run` lagi (hot reload tidak mengganti URL).

## Development: hot reload vs hot restart

| Tombol | Fungsi |
|--------|--------|
| `r` | Hot reload — perubahan UI kecil |
| `R` | Hot restart — disarankan untuk layar login, provider, SnackBar |
| `Ctrl+C` | Stop `flutter run` |

Terminal `flutter run` harus tetap jalan; jangan hanya buka app dari launcher tanpa debug session.

## HP tidak muncul di `flutter devices`

1. Developer options → USB debugging ON.
2. Mode USB: **File transfer (MTP)**, bukan charging only.
3. Terima popup **Allow USB debugging** di HP.
4. `adb kill-server && adb start-server && adb devices` → harus `device`.

## Troubleshooting login / API

| Gejala | Penyebab umum | Solusi |
|--------|----------------|--------|
| SnackBar timeout / "request took longer" | HP tidak menjangkau server | WiFi sama + `serve --host=0.0.0.0`, atau `adb reverse` |
| Laptop WiFi, HP mobile data | Beda jaringan | Pakai `adb reverse` atau sambungkan HP ke WiFi yang sama |
| Login gagal, tidak ada log di Laravel | Request tidak sampai | Periksa URL `--dart-define`, tes browser HP ke IP:8000 |
| `adb devices` kosong | Kabel/debugging | Ganti kabel, izin USB debugging |

Saat login berhasil, terminal Laravel menampilkan: `POST /api/v1/auth/login`.

## Akun demo

Pastikan `php artisan db:seed` sudah dijalankan di backend.

**Registrasi:** layar login → *Belum punya akun? Daftar* (role `user`, otomatis login setelah daftar).

**Login kader:**

- Email: `kader@anugerahbintan.ac.id`
- Password: `12345678`
