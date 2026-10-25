# Web Pengelolaan KIP-K

Aplikasi Laravel untuk mengelola penerima KIP-K, nilai akademik, peringatan IPK, absensi pembinaan, dokumentasi mingguan, notifikasi, dan laporan. Aplikasi memiliki peran admin dan mahasiswa.

## Kebutuhan

- PHP sesuai batas versi di `composer.json`, beserta ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `zip`, dan `gd`.
- Composer dan MySQL/MariaDB.
- Node.js tidak diperlukan untuk menjalankan antarmuka saat ini.

## Menjalankan secara lokal

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Atur `.env` untuk database lokal, zona waktu, dan tiga akun admin demo. Isi `KIPK_ADMIN_1_USERNAME` sampai `KIPK_ADMIN_3_USERNAME` dan kata sandi masing-masing. Seeder akun demo hanya berjalan pada `APP_ENV=local`.

```powershell
php artisan migrate --seed
php artisan serve
```

Buka URL yang ditampilkan oleh `artisan serve`, lalu masuk memakai salah satu akun admin lokal. Akun mahasiswa dibuat oleh admin melalui menu penerima.

## Menjalankan pemeriksaan

```powershell
php artisan test
php vendor/bin/pint --test
php artisan schedule:list
```

## Panduan dan rilis

- Alur pengguna: [docs/panduan-pengguna.md](docs/panduan-pengguna.md)
- Deployment, backup, dan pemulihan: [docs/persiapan-rilis.md](docs/persiapan-rilis.md)
- Flowchart sistem: [flowchart-sistem.md](flowchart-sistem.md)
- Kebutuhan produk: [prd.md](prd.md)

## Keamanan data

KHS dan foto disimpan di disk privat Laravel (`storage/app/private`) dan dilayani melalui controller berotorisasi. Jangan menjalankan `storage:link` untuk berkas tersebut dan jangan menyalin `.env`, database, atau berkas privat ke Git.
