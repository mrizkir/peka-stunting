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

**`https://peka-stunting.anugerahbintan.ac.id/api/v1`**

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

## Splash screen (logo)

Urutan tampil logo saat app dibuka:

1. **Gambar dari backend** — admin unggah di CMS: *Splash screen* (`/settings/splash`)
2. **Fallback lokal** — `assets/images/splash_logo.png` (opsional; letakkan file PNG/JPG di folder itu)
3. **Teks** — kotak putih bertuliskan **PEKA** jika keduanya tidak ada

API: `GET /api/v1/app/splash` → `{ "image_url": "https://..." }` atau `null`. Gambar disimpan lewat **Spatie Media Library** (`App\Models\AppBranding`, koleksi `splash`).

## Troubleshooting

| Gejala | Solusi |
|--------|--------|
| Timeout / tidak bisa login | Cek internet HP; pastikan `https://peka-stunting.anugerahbintan.ac.id` bisa dibuka di browser HP |
| Perlu backend lokal | Pakai `--dart-define=API_BASE_URL=...` (lihat atas) |
| Konten edukasi masih lama setelah edit di CMS | Pastikan status **Published** di backend; di app **tarik ke bawah** (pull refresh) atau ketuk ikon refresh di layar Edukasi/Konten |

**Catatan:** API mobile hanya menampilkan konten berstatus **Published**. Draft tidak muncul di app.

## Build release & distribusi

Aplikasi ini **Android saja** (Flutter). Untuk dibagikan ke kader ada dua jalur umum:

| Jalur | Artefak | Cocok untuk |
|-------|---------|-------------|
| **APK** | file `.apk` | Instal manual (kirim file, link download, MDM) |
| **Google Play** | file `.aab` (App Bundle) | Distribusi lewat Play Store |

Default build release memakai API production (`app_config.dart`) — sama seperti `flutter run` tanpa `--dart-define`.

### Naikkan versi

Edit `pubspec.yaml`:

```yaml
version: 1.0.1+2   # 1.0.1 = versionName, 2 = versionCode (Android)
```

Atau lewat CLI saat build:

```bash
flutter build apk --release --build-name=1.0.1 --build-number=2
```

Setiap upload ke Play Store **wajib** `versionCode` (angka setelah `+`) lebih besar dari rilis sebelumnya.

### Signing release (wajib untuk produksi)

`android/app/build.gradle.kts` sudah dikonfigurasi:

- Ada **`android/key.properties`** → build `release` memakai keystore Anda
- Belum ada → fallback ke **debug key** (cukup uji coba lokal, **bukan** untuk Play Store)

**1. Buat keystore (sekali, simpan backup di tempat aman):**

```bash
keytool -genkey -v -keystore ~/upload-keystore.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias upload
```

**2. File `android/key.properties`** (sudah di-`.gitignore`, jangan di-commit):

```bash
cd mobile/android
cp key.properties.example key.properties
# edit key.properties — isi password dan path keystore
```

Contoh isi:

```properties
storePassword=<password-keystore>
keyPassword=<password-key>
keyAlias=upload
storeFile=/Users/<username>/upload-keystore.jks
```

`storeFile` bisa path absolut atau relatif ke folder `android/` (mis. `upload-keystore.jks` jika file ada di `mobile/android/`).

Panduan resmi: [Flutter — Build and release an Android app](https://docs.flutter.dev/deployment/android).

### Build APK (bagikan langsung ke user)

Semua perintah di bawah dijalankan dari folder `mobile/` (setelah `flutter pub get` jika perlu).

#### APK “fat” (default — jarang dipakai untuk distribusi)

```bash
flutter build apk --release
```

Hasil: `build/app/outputs/flutter-apk/app-release.apk`

Perintah ini membundle **semua** arsitektur CPU (ARM 32/64-bit + x86 untuk emulator), sehingga file bisa **±50–80 MB**. Cocok untuk uji cepat, **bukan** ideal untuk dibagikan ke banyak kader.

#### APK kecil — hanya ARM (disarankan)

Untuk HP kader, biasanya cukup **ARM 64-bit** (`arm64-v8a`). HP modern (±2018 ke atas) memakai ini.

**Opsi A — satu file per arsitektur (paling praktis):**

```bash
flutter build apk --release --split-per-abi
```

Hasil di `build/app/outputs/flutter-apk/`:

| File | Untuk |
|------|--------|
| `app-arm64-v8a-release.apk` | HP Android modern (utama — bagikan ini) |
| `app-armeabi-v7a-release.apk` | HP lama 32-bit (jarang) |
| `app-x86_64-release.apk` | Emulator (abaikan untuk distribusi) |

Ukuran per file biasanya **±20–40 MB** (lebih kecil dari fat APK).

**Opsi B — satu APK, hanya ARM 64-bit:**

```bash
flutter build apk --release --target-platform android-arm64
```

Hasil: `app-release.apk` (satu file, tanpa x86/emulator).

**Opsi C — satu APK, ARM 32 + 64 (tanpa x86):**

```bash
flutter build apk --release --target-platform android-arm,android-arm64
```

Sedikit lebih besar dari opsi B, tetapi mencakup HP lama dan baru dalam satu file.

**Rekomendasi distribusi ke kader:** `app-arm64-v8a-release.apk` dari **opsi A**, atau **opsi B** jika ingin satu nama file saja.

#### Memasang di HP

- Salin APK yang sesuai arsitektur → buka file → izinkan *Install unknown apps* untuk browser/file manager yang dipakai, atau
- Via USB, contoh (ganti nama file):

```bash
adb install -r build/app/outputs/flutter-apk/app-arm64-v8a-release.apk
```

User harus mengizinkan instalasi dari sumber di luar Play Store (pengaturan keamanan Android).

### Build App Bundle (Google Play)

Play Store **tidak** menerima APK upload baru untuk app reguler — gunakan **AAB**:

```bash
flutter build appbundle --release
```

Hasil:

```text
build/app/outputs/bundle/release/app-release.aab
```

### Publish ke Google Play Store

1. **Akun** [Google Play Console](https://play.google.com/console) (biaya pendaftaran developer sekali).
2. **Buat aplikasi** → isi nama, bahasa, kategori (kesehatan/edukasi sesuai konten PEKA).
3. **App signing** — aktifkan *Play App Signing*; unggah AAB; Google menandatangani APK untuk perangkat user.
4. **Store listing** — deskripsi, screenshot (phone), ikon 512×512, feature graphic, kebijakan privasi: `https://peka-stunting.anugerahbintan.ac.id/privacy`. URL hapus akun (Data safety): `https://peka-stunting.anugerahbintan.ac.id/hapus-akun`.
5. **Konten & kepatuhan** — kuesioner rating konten, Data safety (login kader, data kesehatan jika ada).
6. **Testing** — jalur *Internal testing* / *Closed testing* dulu; tambahkan email tester; bagikan link opt-in.
7. **Production** — buat *Release* → unggah `app-release.aab` → review (biasanya beberapa jam–hari).

**Application ID** (tidak boleh diubah setelah rilis pertama): `id.ac.anugerahbintan.pekastunting`.

Checklist sebelum rilis:

- [ ] Keystore & `key.properties` sudah dipakai (bukan debug)
- [ ] `version` di `pubspec.yaml` dinaikkan
- [ ] Login & API ke `https://peka-stunting.anugerahbintan.ac.id` diuji di HP fisik (build `--release`)
- [ ] Akun kader / registrasi berfungsi di production

## Akun demo

Akun harus ada di server production (atau seed di environment yang dipakai).

**Registrasi:** layar login → *Belum punya akun? Daftar*

**Login kader (jika sudah di-seed di server):**

- Email: `kader@anugerahbintan.ac.id`
- Password: `12345678`
