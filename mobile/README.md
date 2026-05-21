# PEKA Stunting — Mobile

Aplikasi Android (Flutter) untuk kader.

## Stack

- Flutter 3
- Riverpod
- Dio
- go_router
- shared_preferences

## API server

Default (tanpa konfigurasi tambahan):

**`https://peka-stunting.yacanet.com/api/v1`**

Didefinisikan di `lib/core/config/app_config.dart`. HP cukup **internet** (WiFi atau mobile data); tidak perlu backend lokal atau `adb reverse`.

## Menjalankan aplikasi

```bash
cd mobile
flutter pub get
flutter devices
flutter run -d <device_id>
```

Contoh:

```bash
flutter run -d RR8N608YY2F
```

### Emulator / HP fisik

Perintah sama — langsung ke server production. Tidak perlu `--dart-define`.

## Development lokal (opsional)

Hanya jika ingin mengarahkan ke Laravel di laptop:

```bash
# Backend lokal
cd backend
php artisan serve --host=127.0.0.1 --port=8000

# HP via USB (mobile data + laptop WiFi beda jaringan)
adb reverse tcp:8000 tcp:8000

# Run dengan override URL
flutter run -d <device_id> \
  --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
```

Atau WiFi sama: `--dart-define=API_BASE_URL=http://<IP-laptop>:8000/api/v1`

## Development: hot reload vs hot restart

| Tombol | Fungsi |
|--------|--------|
| `r` | Hot reload |
| `R` | Hot restart (disarankan untuk login / provider) |
| `Ctrl+C` | Stop `flutter run` |

## ADB (Android Debug Bridge)

**Bukan** `adb list` — perintah yang benar:

```bash
adb devices
```

Contoh output:

```text
List of devices attached
RR8N608YY2F    device
```

Ganti `RR8N608YY2F` dengan ID di kolom pertama saat `flutter run -d <device_id>`.

| Perintah | Fungsi |
|----------|--------|
| `adb devices` | Daftar HP/emulator terhubung |
| `adb kill-server && adb start-server` | Restart ADB |
| `adb reverse tcp:8000 tcp:8000` | Forward port backend ke HP (dev lokal via USB) |
| `flutter devices` | Daftar device untuk Flutter (sama, lebih ringkas) |

| Status `adb devices` | Artinya |
|----------------------|---------|
| `device` | Siap dipakai |
| `unauthorized` | Buka HP → Allow USB debugging |
| (kosong) | Lihat checklist di bawah |

## HP tidak muncul di `flutter devices`

1. Developer options → USB debugging ON.
2. Mode USB: **File transfer (MTP)**, bukan charging only.
3. Terima popup **Allow USB debugging** (+ *Always allow*).
4. `adb kill-server && adb start-server && adb devices` → harus `device`.

## Troubleshooting

| Gejala | Solusi |
|--------|--------|
| Timeout / tidak bisa login | Cek internet HP; pastikan `https://peka-stunting.yacanet.com` bisa dibuka di browser HP |
| Perlu backend lokal | Pakai `--dart-define=API_BASE_URL=...` (lihat atas) |
| Konten edukasi masih lama setelah edit di CMS | Pastikan status **Published** di backend; di app **tarik ke bawah** (pull refresh) atau ketuk ikon refresh di layar Edukasi/Konten |

**Catatan:** API mobile hanya menampilkan konten berstatus **Published**. Draft tidak muncul di app.

## Akun demo

Akun harus ada di server production (atau seed di environment yang dipakai).

**Registrasi:** layar login → *Belum punya akun? Daftar*

**Login kader (jika sudah di-seed di server):**

- Email: `kader@anugerahbintan.ac.id`
- Password: `12345678`
