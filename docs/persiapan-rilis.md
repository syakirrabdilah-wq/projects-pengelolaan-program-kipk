# Persiapan Rilis, Backup, dan Pemulihan

Dokumen ini menyiapkan langkah rilis. Deployment baru dianggap selesai setelah langkahnya dijalankan dan dicatat pada server tujuan.

## Konfigurasi produksi

1. Arahkan document root web server ke direktori `public/`, aktifkan HTTPS, dan pastikan direktori proyek lain tidak dapat diakses melalui web.
2. Buat `.env` khusus server. Atur `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` memakai HTTPS, `APP_KEY`, koneksi MySQL, `APP_TIMEZONE`, dan nama instansi. Jangan menyalin `.env` lokal ke server atau Git.
3. Atur izin tulis hanya untuk akun layanan web pada `storage/` dan `bootstrap/cache/`. Pertahankan `storage/app/private` di luar document root.
4. Pasang PHP CLI dan `mysqldump` pada server. Isi `KIPK_MYSQLDUMP_BINARY` dengan lokasi executable bila tidak tersedia di `PATH`.
5. Atur `KIPK_BACKUP_PATH` ke volume backup yang tidak dilayani web dan bukan `storage/app/private`. Atur `KIPK_BACKUP_PASSWORD` dengan rahasia acak minimal 32 karakter, simpan terpisah dari server, dan batasi aksesnya.

## Rilis aplikasi

```sh
composer install --no-dev --optimize-autoloader
php artisan down
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan kipk:admin-create admin1
php artisan kipk:admin-create admin2
php artisan kipk:admin-create admin3
php artisan up
```

Perintah `kipk:admin-create` meminta kata sandi secara tersembunyi dan meminta konfirmasi. Jalankan dari terminal server yang tepercaya. Jangan memakai `db:seed` pada produksi; seeder admin demo hanya untuk lingkungan lokal.

Jalankan scheduler Laravel setiap menit memakai cron:

```cron
* * * * * cd /path/aplikasi && php artisan schedule:run >> /dev/null 2>&1
```

Jadwal backup harian dimulai pukul 01.15 sesuai `APP_TIMEZONE`. Periksa dengan `php artisan schedule:list` setelah deploy.

## Membuat dan menyimpan backup

Perintah `php artisan kipk:backup` membuat arsip ZIP dengan enkripsi AES-256 yang berisi dump MySQL dan seluruh berkas privat. Arsip berada di `KIPK_BACKUP_PATH`. Dump SQL sementara di `storage/app/tmp-backup` akan dihapus setelah perintah selesai; tempatkan direktori `storage/` pada volume terenkripsi dan pastikan hanya akun layanan yang dapat membacanya.

Setelah backup harian pertama:

1. Pastikan perintah berhasil dan arsip ZIP tidak kosong.
2. Uji arsip dengan `7z t <nama-arsip.zip> -p<kata-sandi-backup>`.
3. Salin arsip terenkripsi ke lokasi berbeda dari server aplikasi, misalnya penyimpanan off-site yang aksesnya dibatasi.
4. Simpan kata sandi backup pada password manager atau secret manager terpisah. Kehilangan kata sandi membuat arsip tidak dapat dipulihkan.
5. Pantau log scheduler dan ruang disk. Hapus arsip lama sesuai kebijakan retensi kampus setelah salinan off-site diverifikasi.

## Uji pemulihan

Lakukan pada server staging dengan database dan direktori privat kosong; jangan menguji restore pada produksi.

1. Ambil satu arsip dari lokasi off-site dan jalankan `7z t`.
2. Ekstrak menggunakan kata sandi backup ke direktori sementara yang aksesnya dibatasi.
3. Buat database staging kosong, lalu pulihkan `database.sql` menggunakan `mysql`.
4. Pulihkan isi direktori `private/` ke `storage/app/private/` staging.
5. Atur `.env` staging agar hanya menunjuk ke database dan berkas staging, kemudian bersihkan cache konfigurasi.
6. Login sebagai admin dan mahasiswa uji. Periksa dashboard, nilai/KHS, foto absensi, notifikasi, dan laporan.
7. Catat tanggal backup, waktu pemulihan, hasil pemeriksaan, dan masalah yang ditemukan. Ulangi setelah perubahan besar pada database atau penyimpanan.

Jangan tandai tugas deployment atau backup sebagai selesai sampai scheduler aktif, salinan off-site tersedia, dan pemulihan staging berhasil.
