# Tech Stack — Website Manajemen Penerima KIP-K

> Pilihan teknologi beserta alasannya. Bila ada perubahan, perbarui dokumen ini dan catat alasannya di bagian "Catatan keputusan".

## 1. Ringkasan

| Lapisan | Pilihan | Alasan singkat |
|---|---|---|
| Bahasa backend | PHP 8.3+ (Laravel 13) atau 8.2+ (Laravel 12) | Sejalan dengan pengalaman PHP/JS dan MySQL sebelumnya; hosting mudah |
| Framework | Laravel (versi stabil terbaru) | Auth, validasi, migrasi, upload, scheduler, dan otorisasi sudah tersedia |
| Database | MySQL 8 / MariaDB 10.5+ | Skema sudah dirancang (`database/kipk_schema.sql`) |
| Templating | Blade | Server-rendered, sederhana, tidak perlu SPA |
| CSS | Bootstrap 5 (CDN dulu, bisa dipindah ke npm) | Komponen siap pakai, responsif, mudah dipelajari |
| JavaScript | JS biasa + Chart.js | Grafik dashboard dan interaksi ringan |
| Autentikasi | Auth bawaan Laravel (guard session), login manual memakai `username`, tanpa starter kit | Starter kit resmi sekarang berbasis React/Vue/Livewire dan menyertakan registrasi mandiri yang tidak dibutuhkan; kebutuhan kita hanya login dan keluar |
| Otorisasi | Middleware peran + Laravel Policies | Memastikan mahasiswa hanya mengakses datanya |
| Impor/ekspor Excel | `maatwebsite/excel` | Impor penerima dan ekspor laporan |
| Ekspor PDF | `barryvdh/laravel-dompdf` | Laporan PDF |
| Penyimpanan berkas | Disk privat Laravel (`storage/app/private`) | KHS dan foto tidak boleh publik |
| Pengolahan gambar | Intervention Image (opsional) | Kompres dan ubah ukuran foto |
| Penjadwalan | Laravel Scheduler (cron) | Backup berkala dan tugas terjadwal |
| Pengujian | PHPUnit / Pest | Uji fitur dan unit |
| Kualitas kode | Laravel Pint | Format kode otomatis |
| Versi kode | Git + GitHub | Riwayat dan kolaborasi |

> Versi pasti (Laravel dan paket) ditentukan saat setup awal dengan `composer create-project` dan dikunci di `composer.lock`. Periksa versi terbaru yang didukung sebelum memulai.

## 2. Lingkungan pengembangan

- **Lokal:** Laragon atau XAMPP (PHP sesuai versi Laravel di atas, MySQL), Composer, Git. Node.js LTS opsional (hanya bila aset dibangun dengan Vite). Aktifkan ekstensi PHP `gd`, `zip`, `fileinfo`, `pdo_mysql`, `mbstring`.
- **Editor:** VS Code dengan ekstensi PHP Intelephense dan Laravel Blade.
- **Database tool:** phpMyAdmin atau DBeaver.
- **Konfigurasi:** semua rahasia di `.env` (tidak di-commit); sediakan `.env.example`.

## 3. Lingkungan produksi

| Aspek | Rekomendasi |
|---|---|
| Server | VPS kecil atau hosting yang mendukung PHP 8.2+ dan akses SSH/cron |
| Web server | Nginx atau Apache dengan document root ke `public/` |
| HTTPS | Wajib (Let's Encrypt) |
| Cron | `* * * * * php /path/artisan schedule:run` |
| Backup | Dump database harian dan salinan folder `storage/app/private` |
| Mode | `APP_ENV=production`, `APP_DEBUG=false` |

## 4. Mengapa bukan alternatif lain

| Alternatif | Pertimbangan |
|---|---|
| PHP native (tanpa framework) | Cocok untuk belajar dasar, tetapi autentikasi, validasi, CSRF, upload aman, dan otorisasi harus ditulis sendiri sehingga rawan celah |
| Next.js / Node + SPA | Butuh API terpisah dan lebih banyak komponen; tidak sebanding untuk kebutuhan CRUD berbasis peran ini |
| Django / Flask | Baik, tetapi di luar pengalaman PHP yang sudah ada |

## 5. Dependensi utama (perkiraan)

```bash
composer create-project laravel/laravel kipk
composer require maatwebsite/excel barryvdh/laravel-dompdf
composer require intervention/image   # opsional
# Tidak memakai starter kit; login dibuat manual (lihat panduan-memulai.md)
```

## 6. Catatan keputusan

| Tanggal | Keputusan | Alasan |
|---|---|---|
| 2026-10-24 | Memakai Laravel + Blade + Bootstrap | Menyeimbangkan kecepatan belajar dan keamanan bawaan framework |
| 2026-10-24 | Berkas KHS dan foto disimpan di disk privat | BR-07: data pribadi tidak boleh diakses lewat URL publik |
| 2026-10-24 | Notifikasi WhatsApp/email ditunda | Di luar lingkup rilis pertama (lihat `prd.md`) |
| 2026-10-24 | Tanpa starter kit/Breeze; login manual dengan `username` | Hasil pengecekan: starter kit Laravel terbaru berbasis React/Vue/Livewire, bukan Blade polos |
