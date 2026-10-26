# Plan — Rencana Pengembangan

> Rencana per fase. Daftar tugas rinci ada di `task.md`. Estimasi durasi untuk satu pengembang dengan waktu paruh; sesuaikan dengan kondisi sebenarnya.

## Ringkasan fase

| Fase | Nama | Estimasi | Hasil utama |
|---|---|---|---|
| 0 | Persiapan | 3–4 hari | Repo, lingkungan, kerangka Laravel, skema database terpasang |
| 1 | Autentikasi dan data penerima | 1 minggu | Login berperan, CRUD penerima, impor Excel |
| 2 | Nilai akademik dan peringatan | 1–1,5 minggu | Input nilai + KHS oleh admin, aturan IPK, peringatan otomatis, tampilan nilai mahasiswa |
| 3 | Pembinaan dan absensi | 1 minggu | Kegiatan, absensi dengan jendela waktu dan foto bertimestamp, rekap |
| 4 | Dashboard, laporan, notifikasi | 1 minggu | Dashboard kedua peran, ekspor PDF/Excel |
| 5 | Pengerasan dan rilis | 1 minggu | Pengujian menyeluruh, keamanan, deploy, backup |

Total perkiraan: sekitar 6–8 minggu.

## MVP

Fase 0–3 menghasilkan MVP yang dapat dipakai: login, data penerima, nilai, peringatan, dan absensi dengan foto bukti. Fase 4–5 menyempurnakan dan merilis.

---

## Fase 0 — Persiapan
**Tujuan:** lingkungan kerja siap dan skema terpasang.
- Inisialisasi repositori Git dan struktur dokumen.
- Pasang Laravel (tanpa starter kit), paket Excel dan PDF.
- Konfigurasi `.env`, database lokal, dan disk privat.
- Terjemahkan `kipk_schema.sql` menjadi migrasi.
- Siapkan layout dasar Blade (admin dan mahasiswa) dengan Bootstrap.

**Kriteria selesai:** `php artisan migrate --seed` berhasil; halaman kosong untuk kedua layout tampil.

## Fase 1 — Autentikasi dan data penerima
**Tujuan:** pengguna dapat login sesuai peran dan admin mengelola penerima.
- Login dengan `username`, middleware peran, pembatasan percobaan login.
- CRUD penerima beserta pembuatan akun mahasiswa.
- Pencarian, filter, paginasi; ubah status KIP-K.
- Tiga akun admin setara (dibuat lewat seeder/perintah, bukan ditulis di kode).
- Impor Excel dengan validasi dan laporan baris galat.

**Kriteria selesai:** admin dan mahasiswa masuk ke dashboard masing-masing; mahasiswa tidak bisa membuka rute admin; impor contoh 50 baris berhasil.

## Fase 2 — Nilai akademik dan peringatan
**Tujuan:** inti nilai dan peringatan otomatis berjalan.
- Form nilai per semester dan unggah KHS (PDF privat) oleh admin.
- Pengelolaan `aturan_prestasi` dengan tanggal berlaku.
- `PeringatanService` dengan tingkat berurutan (1-2 pesan evaluasi, tingkat 3 ke atas ditandai pencabutan) dan notifikasi.
- Tombol Cabut KIP-K oleh admin dengan konfirmasi.
- Halaman Pengaturan admin (batas pencabutan).
- Halaman "Nilai saya" read-only beserta grafik dan unduh KHS sendiri.

**Kriteria selesai:** menyimpan IPK 2,90 membuat peringatan dan notifikasi; IPK 3,20 tidak; tiga semester berturut-turut di bawah batas menandai pencabutan; mahasiswa tidak bisa mengubah atau melihat nilai orang lain.

## Fase 3 — Pembinaan dan absensi
**Tujuan:** kehadiran tercatat online dengan aman.
- CRUD kegiatan pembinaan dengan jam buka dan tutup.
- Tombol absen mahasiswa dengan foto wajib, timestamp server, jendela waktu, dan satu kali per sesi.
- Rekap kehadiran per kegiatan dan per mahasiswa.
- Pengamanan absensi memakai jendela waktu dan foto; tanpa kode sesi, QR, atau geolokasi.

**Kriteria selesai:** absen di luar jam atau tanpa foto valid ditolak; admin meninjau foto dari mahasiswa dan mengoreksi status tanpa mengunggah foto baru; timestamp berasal dari server; absen ganda ditolak; rekap sesuai data.

## Fase 4 — Dashboard, laporan, notifikasi
**Tujuan:** ringkasan dan keluaran untuk pimpinan.
- Dashboard admin: kartu ringkasan, grafik, daftar bermasalah.
- Dashboard mahasiswa: IPK, status, banner peringatan, dan kehadiran.
- Ekspor PDF/Excel untuk nilai dan kehadiran.
- Halaman notifikasi dengan penandaan sudah dibaca.

**Kriteria selesai:** laporan semester dapat diekspor dalam < 5 menit kerja admin.

## Fase 5 — Pengerasan dan rilis
**Tujuan:** siap dipakai sungguhan.
- Tinjau keamanan (otorisasi, unggahan, header, rate limit).
- Tes menyeluruh dan uji pengguna dengan beberapa mahasiswa dan admin.
- Optimasi kueri dan indeks.
- Deploy ke server, HTTPS, cron, backup harian.
- Panduan pengguna singkat untuk admin dan mahasiswa.

**Kriteria selesai:** daftar periksa rilis di bawah terpenuhi.

### Daftar periksa rilis
- [ ] `APP_DEBUG=false`, rahasia di `.env` server.
- [ ] HTTPS aktif; cron penjadwal berjalan.
- [ ] Backup database dan folder privat teruji pulih.
- [ ] Tes otorisasi lulus.
- [ ] Akun admin awal dibuat dengan password kuat.

## Risiko dan keputusan tertunda

| Item | Dampak | Rencana |
|---|---|---|
| Semester tanpa data (mis. cuti) memutus rangkaian berurutan | Fase 2 | Admin menginput semua semester; konfirmasi perlakuan khusus (Q-08) |
| Foto absensi tidak membuktikan lokasi atau waktu pengambilan | Fase 3 | Waktu server dicatat saat file diterima; absensi tetap dibatasi jendela waktu |
| Skala data nyata | Fase 6 | Uji dengan data simulasi ±500 mahasiswa |
