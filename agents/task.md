# Task — Daftar Tugas

> Legenda status: `[ ]` belum · `[~]` dikerjakan · `[x]` selesai · `[!]` terblokir.
> Setiap tugas merujuk kebutuhan di `prd.md` (FR-xx / BR-xx). Aturan pengerjaan ada di `agents/agents.md` dan `rules.md`.

## Fase 0 — Persiapan

- [x] T-000 Rancang kebutuhan, ERD, dan skema SQL (`prd.md`, `database/kipk_schema.sql`)
- [x] T-001 Susun dokumen proyek (`prd.md`, `techstack.md`, `design.md`, `rules.md`, `agents/*`)
- [x] T-002 Inisialisasi repositori Git, `.gitignore`, `.env.example`
- [x] T-003 Pasang Laravel (tanpa starter kit), `maatwebsite/excel`, `dompdf`; cek versi PHP dan ekstensi
- [x] T-004 Konfigurasi database lokal dan disk privat (`storage/app/private`)
- [x] T-005 Buat migrasi dari `kipk_schema.sql` untuk 10 tabel (termasuk `pengaturan`)
- [x] T-006 Seeder: aturan IPK awal 3,00, nilai awal tabel `pengaturan`, dan tiga akun admin (kata sandi dari `.env`; akun demo hanya untuk lokal)
- [x] T-007 Layout dasar Blade admin dan mahasiswa (Bootstrap 5)

## Fase 1 — Autentikasi dan data penerima

- [x] T-101 Login memakai `username` + middleware `EnsureRole` (FR-01)
- [x] T-102 Pembatasan percobaan login dan halaman keluar
- [x] T-103 CRUD penerima + pembuatan akun mahasiswa otomatis (FR-02)
- [x] T-104 Pencarian, filter (prodi, angkatan, status), paginasi (FR-02)
- [x] T-105 Impor Excel/CSV dengan validasi baris dan laporan galat (FR-03)
- [x] T-106 Manajemen akun untuk 3 admin dan mahasiswa: reset password, nonaktifkan (FR-18)
- [x] T-107 Tes: rute admin tertutup untuk mahasiswa

## Fase 2 — Nilai akademik dan peringatan

- [x] T-201 Form nilai per semester: IPS, IPK, SKS (rentang 0–4) (FR-04, BR-01)
- [x] T-202 Unggah KHS PDF ke disk privat dan unduh lewat controller (FR-04, BR-07)
- [x] T-203 CRUD aturan prestasi dengan `berlaku_mulai` (FR-06)
- [x] T-204 `PeringatanService`: evaluasi, tingkat berurutan (reset saat IPK memenuhi batas), upsert, hapus dan nomor ulang bila nilai dikoreksi (FR-07, BR-03, BR-04, BR-08)
- [x] T-205 `NotifikasiService` dan notifikasi peringatan (FR-08)
- [x] T-206 Halaman "Nilai saya" read-only + grafik + unduh KHS sendiri (FR-05)
- [x] T-207 Indikator posisi IPK terhadap batas di halaman mahasiswa (FR-06)
- [x] T-208 Halaman admin Peringatan (FR-07)
- [x] T-209 Tes unit `PeringatanService` (di bawah, tepat di batas, di atas, tingkat 1-2-3 berurutan, reset saat memenuhi batas, semester tanpa data memutus rangkaian, koreksi nilai)
- [x] T-210 Tes otorisasi: mahasiswa tidak bisa menulis nilai maupun melihat nilai orang lain (BR-01, BR-02)
- [x] T-211 Halaman Pengaturan admin: hari dan jam tenggat, batas pencabutan (FR-20)
- [x] T-212 Penanda pencabutan (tingkat di atas atau sama dengan batas), notifikasi ke semua admin, dan tombol Cabut KIP-K dengan konfirmasi (BR-08)

## Fase 3 — Pembinaan dan absensi

- [x] T-301 Keputusan awal: absensi memakai jendela waktu saja (2026-10-24; diperbarui T-307)
- [x] T-302 CRUD kegiatan pembinaan dengan jam buka dan tutup (FR-09)
- [x] T-303 `AbsensiService` + tombol absen mahasiswa (FR-10, BR-05)
- [x] T-304 Rekap kehadiran per kegiatan dan per mahasiswa (FR-11)
- [x] T-305 Koreksi status absensi oleh admin (hadir/izin/alpa)
- [x] T-306 Tes: absen di luar jam ditolak; absen ganda ditolak
- [x] T-307 Foto wajib untuk absensi mahasiswa; admin meninjau foto kiriman dan mengoreksi status tanpa unggah foto baru (FR-10, BR-05, BR-07)

## Fase 4 — Dokumentasi mingguan (fitur dihentikan 2026-10-26)

Tugas di bawah ini adalah riwayat implementasi. Alur unggah, verifikasi, pengingat, dan laporan dokumentasi mingguan telah dihapus; bukti foto dikirim saat mahasiswa absen.

- [x] T-401 Form unggah foto dengan pratinjau dan validasi JPG/PNG ≤ 5 MB (FR-12)
- [x] T-402 `DokumentasiService`: tentukan minggu ISO, satu kiriman per minggu, kirim ulang bila ditolak (BR-06)
- [x] T-403 Antrean verifikasi admin: terima/tolak + catatan (FR-13)
- [x] T-404 Notifikasi hasil verifikasi (FR-08)
- [x] T-405 Perintah terjadwal `KirimPengingatDokumentasi` (FR-14)
- [x] T-406 Keputusan: tenggat ditentukan admin lewat halaman Pengaturan (dikerjakan di T-211)
- [x] T-407 Tes: kiriman ganda ditolak; akses foto orang lain ditolak
- [x] T-408 Penandaan kiriman terlambat berdasarkan tenggat di Pengaturan (BR-06)

## Fase 5 — Dashboard, laporan, notifikasi

- [x] T-501 Dashboard admin: 5 kartu ringkasan, grafik per prodi/angkatan, dan daftar mahasiswa bermasalah (FR-15)
- [x] T-502 Dashboard mahasiswa: IPK, status, banner peringatan, dan kehadiran (FR-16)
- [x] T-503 Ekspor Excel: rekap nilai dan kehadiran dengan filter (FR-17)
- [x] T-504 Ekspor PDF nilai per semester, admin saja (FR-17)
- [x] T-505 Halaman notifikasi kedua peran, tandai sudah dibaca, dan jumlah belum dibaca di navbar (FR-08)
- [x] T-506 Audit log nilai, status KIP-K, aturan prestasi, dan pengaturan (FR-19, disetujui pemilik)

## Fase 6 — Pengerasan dan rilis

- [x] T-601 Tinjauan keamanan: otorisasi, unggahan, header, rate limit
- [ ] T-602 Uji alur lengkap di ponsel (≥ 360 px) dan desktop
- [x] T-603 Tambahkan indeks kueri dan ukur beban dashboard dengan data simulasi 500 mahasiswa (query count; target waktu respons produksi masih perlu diuji)
- [ ] T-604 Deploy: HTTPS, `APP_DEBUG=false`, cron penjadwal
- [ ] T-605 Backup harian database + folder privat, uji pemulihan
- [x] T-606 Panduan singkat pengguna (admin dan mahasiswa)
- [ ] T-607 Uji pengguna dengan beberapa admin dan mahasiswa, catat masukan

## Keputusan dan pertanyaan

| ID | Topik | Terkait | Status |
|---|---|---|---|
| Q-01 | Tenggat dokumentasi mingguan | T-406 | Selesai: diatur admin di halaman Pengaturan |
| Q-02 | Pengamanan absensi | T-301, T-307 | Diperbarui: jendela waktu dan foto kegiatan wajib; timestamp dari waktu server saat foto diterima, tanpa QR/geolokasi |
| Q-03 | Aturan tingkat peringatan | T-204 | Selesai: 1x dan 2x pesan peringatan evaluasi, pencabutan mulai kejadian ke-3 dan dikonfirmasi admin, dihitung berurutan |
| Q-04 | Jumlah admin | T-106 | Selesai: tiga admin |
| Q-05 | Keberatan nilai atau peringatan | PRD bagian 11 | Selesai: di luar sistem (WhatsApp pribadi) |
| Q-06 | Hitungan peringatan: kumulatif atau berurutan? | T-204 | Selesai: berurutan (2026-10-24) |
| Q-08 | Semester tanpa data (mis. cuti) memutus rangkaian berurutan; apakah perlu aturan khusus? | T-204 | _menunggu_ (asumsi: memutus rangkaian; admin menginput semua semester) |
| Q-07 | Perlakuan dokumentasi setelah tenggat | T-408 | Selesai: tetap diterima dan ditandai terlambat (2026-10-25) |

## Catatan agent

- 2026-10-25 Â· T-106, T-107 Â· Ditambahkan halaman manajemen akun admin/mahasiswa: pencarian dan filter peran, reset password dengan konfirmasi, serta aktif/nonaktif. Akun sendiri tidak dapat dinonaktifkan; akun nonaktif ditolak middleware. Rute khusus admin dan navigasi admin diperbarui. Tes: `php artisan test --filter=AkunTest` (5 tes, 22 asersi); Pint lulus.

> Tulis serah terima di sini: tanggal, `T-xxx`, apa yang selesai, file yang disentuh, asumsi, dan cara menjalankan tes.

- 2026-10-24 · T-000, T-001 · Dokumen awal dan skema SQL dibuat. Belum ada kode aplikasi. Mulai dari T-002.
- 2026-10-24 · PRD bagian 11 dijawab pemilik proyek. Dokumen, skema (tabel `pengaturan`), dan tugas disesuaikan. Asumsi sementara ada di Q-06 dan Q-07.
- 2026-10-24 · Dikonfirmasi: pencabutan mulai kejadian ke-3 dan dikonfirmasi admin (tidak otomatis). Q-06 tersisa soal kumulatif/berurutan; Q-07 belum terjawab jelas.
- 2026-10-24 · Dikonfirmasi: peringatan dihitung berurutan. Q-06 selesai. Tersisa Q-07 (dokumentasi lewat tenggat) dan Q-08 (semester tanpa data).
- 2026-10-25 · T-103–T-105 · CRUD penerima admin, akun mahasiswa otomatis (username = NIM), filter/paginasi, dan impor XLSX/XLS/CSV dengan validasi serta laporan per baris. File utama: `PenerimaController`, `PenerimaRequest`, `ImportPenerimaRequest`, `PenerimaImport`, model/migrasi `Mahasiswa`, rute dan Blade admin. Admin mengisi password awal minimal 8 karakter; impor memakai kolom `password` dan tidak menyimpan file. Status `dicabut` memerlukan konfirmasi di form atau kolom `konfirmasi_pencabutan` pada impor. Tes: `php artisan test`; Pint: `php vendor/bin/pint` pada file PHP yang berubah. Catatan: T-101 (login) dan bagian lain T-005 masih belum dikerjakan; akses rute memakai `auth` + `role:admin` dan tes otorisasi menggunakan sesi uji.
- 2026-10-25 · T-201, T-202, T-203, T-204, T-209 · Nilai admin dengan validasi dan pencatatan penginput, KHS PDF privat, CRUD aturan berlaku, sinkronisasi peringatan berurutan, dan tes unit/fitur. Migrasi yang ditambahkan: `semester_akademik`, `aturan_prestasi`, `peringatan`, `pengaturan`; `created_at` nilai menjadi tanggal evaluasi. Aturan historis tidak dapat dihapus; ambang pencabutan dibaca dari Pengaturan dan tidak mengubah status atau membuat notifikasi. KHS diunduh lewat controller admin/pemilik. Tes: `php artisan test`; Pint dijalankan pada seluruh PHP yang berubah. T-205 notifikasi belum dikerjakan; T-005 masih belum mencakup tabel lain dan T-101 login masih belum dikerjakan.
- 2026-10-25 · T-205–T-208, T-210–T-212 · Ditambahkan notifikasi peringatan untuk mahasiswa dan notifikasi tingkat ambang ke semua admin, halaman nilai mahasiswa yang hanya membaca sesi login, grafik IPK dan posisi terhadap batas, halaman peringatan admin, pengaturan tenggat/batas, serta pencabutan manual dengan konfirmasi. Tabel `notifikasi` dibuat sesuai skema; seed pengaturan awal Minggu 23:59 dan ambang 3. Tes mencakup isolasi data mahasiswa, larangan rute tulis, batas peran, validasi pengaturan, ambang pencabutan, konfirmasi, dan penerima notifikasi. T-101 login masih belum dikerjakan; halaman admin belum bisa diuji lewat login browser sampai tugas itu selesai. Jalankan `php artisan test` dan `php vendor/bin/pint`.
- 2026-10-25 · T-302–T-306 · Ditambahkan CRUD kegiatan dengan pembuat dari admin login dan constraint rentang waktu, `AbsensiService` berbasis zona waktu aplikasi, absensi sekali per kegiatan dengan unique key, rekap kegiatan/mahasiswa, serta koreksi admin untuk status hadir/izin/alpa. Mahasiswa hanya memakai identitas dari sesi dan hanya dapat membuka rekap sendiri. Rekap persentase menghitung kegiatan yang sudah ditutup. Tes mencakup batas buka/tutup, duplikasi, isolasi, rekap, constraint, validasi, dan peran. Default zona waktu aplikasi `Asia/Jakarta` dapat ditimpa lewat `APP_TIMEZONE`; pengujian dengan SQLite memakai trigger untuk menegakkan batas waktu. `php artisan test` dan Pint dijalankan. Uji browser admin menunggu login T-101.
- Keputusan Q-07 dikonfirmasi 2026-10-25: dokumentasi yang dikirim setelah tenggat tetap diterima dan ditandai terlambat berdasarkan Pengaturan.
- 2026-10-25 · T-401–T-405, T-407–T-408 · Ditambahkan unggah JPG/PNG tervalidasi dari MIME asli, pratinjau, kompresi lebar, simpan privat, pengiriman ulang setelah penolakan, antrean/verifikasi admin, notifikasi hasil, penjadwal pengingat harian yang deduplikasi per mahasiswa/hari, dan penanda terlambat dari `created_at`. Pengingat dikirim pada hari tenggat sebelum jamnya. Tes: `php artisan test`; Pint dijalankan pada semua file PHP terkait; jadwal dapat diperiksa dengan `php artisan schedule:list`. Uji browser menunggu login T-101.
- 2026-10-25 · T-501, T-502, T-505 · Dashboard admin menampilkan kartu ringkasan, grafik Chart.js per prodi/angkatan, serta mahasiswa dengan IPK rendah, kehadiran rendah (<75% kegiatan selesai; disetujui pemilik), atau dokumentasi mingguan yang belum dikirim. Dashboard mahasiswa memakai identitas sesi untuk menampilkan ringkasan miliknya. Notifikasi dibatasi ke pemilik, dapat ditandai dibaca, dan jumlah belum dibaca tampil di navbar. Tes otorisasi dan keadaan kosong ditambahkan. Verifikasi: `php artisan test` (69 tes lulus, 300 asersi) dan `php vendor/bin/pint --test` lulus. Uji manual responsif: buka dashboard/notifikasi pada viewport 360 px dan desktop; cek grafik, navigasi, dan keadaan kosong. Login browser masih menunggu T-101.
- 2026-10-25 · T-503, T-504 · Ditambahkan ekspor XLSX nilai, kehadiran (termasuk aktif yang belum hadir), dan dokumentasi memakai query ber-chunk 500 baris; filter prodi/angkatan untuk semua, semester untuk nilai. Kolom jalur KHS/foto privat tidak diekspor. Ditambahkan laporan nilai semester PDF dengan nama instansi dari `KIPK_NAMA_INSTANSI`, tanggal cetak, tabel berulang per halaman, filter prodi/angkatan, dan rute admin saja. Tes ekspor, filter, otorisasi, isi PDF, sanitasi formula spreadsheet, dan eksklusi jalur privat ditambahkan. Verifikasi saat tugas ekspor: `php artisan test` (72 tes lulus, 331 asersi) dan `php vendor/bin/pint --test` lulus. Uji manual buat data 500+ mahasiswa dan beberapa semester, lalu coba seluruh jenis ekspor serta filter.
- 2026-10-25 · T-506 · Atas persetujuan pemilik, ditambahkan migrasi/model audit log serta observer untuk pembuatan, perubahan, dan penghapusan nilai semester; perubahan status KIP-K; aturan prestasi; dan pengaturan. Log menyimpan ID aktor admin, jenis/ID data, nama field, nilai sebelum-sesudah, aksi, dan waktu; tidak menyimpan NIM, password, atau jalur berkas privat. Perubahan dari aktor non-admin tidak dicatat. Tes memeriksa empat jenis data, aktor/waktu, field allowlist, dan operasi tanpa admin. Verifikasi: `php artisan test` (75 tes lulus, 346 asersi) dan `php vendor/bin/pint --test` lulus.
- 2026-10-25 · T-101, T-102, T-601 · Login manual username tanpa starter kit, regenerasi/invalidation sesi, penolakan akun nonaktif dan perpindahan akun, serta pembatasan 5 percobaan gagal per IP per 60 detik. Audit rute dan tampilan memastikan rute bernilai/admin dibatasi peran, identitas mahasiswa berasal dari sesi, berkas privat dilayani controller berotorisasi, form web memakai CSRF, dan Blade tidak memakai raw output. Ditambahkan header keamanan/CSP dengan nonce skrip, cookie sesi Secure default untuk production, batas 8 juta piksel sebelum decode foto, dan impor penerima berbasis chunk. Verifikasi: `php artisan test` (82 tes, 403 asersi) dan `php vendor/bin/pint --test` lulus.
- 2026-10-25 · T-002–T-007 · Verifikasi repositori, `.gitignore`, `.env.example`, dependensi Laravel/Excel/dompdf, PHP 8.2 dan ekstensi yang dibutuhkan, koneksi MySQL `db_kipk`, disk privat, seluruh migrasi, dan layout bersama admin/mahasiswa. Skema dipindah ke `database/kipk_schema.sql` dan nama DB di SQL diselaraskan dengan `.env.example`. Seeder lokal membuat tiga akun admin dari password `.env` yang di-hash, dan tidak membuat akun di luar `local`; password acak lokal disimpan hanya di `.env` yang diabaikan Git. Seeder referensi dan admin berhasil dijalankan; database menampilkan `admin1`–`admin3`. Tes: 3 tes seeder, seluruh suite (85 tes, 415 asersi), Pint lulus.
- 2026-10-25 · T-307 · Absensi mandiri mahasiswa dan koreksi admin kini wajib menyertakan foto JPG/PNG maks. 5 MB; waktu dicatat server saat file diterima. Foto disimpan privat dengan nama acak dan hanya dapat dilihat admin atau pemilik melalui controller. Ditambahkan validasi dimensi, form unggah, tampilan bukti/timestamp, migrasi `file_foto`, tes validasi, timestamp, penggantian foto, dan otorisasi. Verifikasi: `php artisan test` (86 tes, 443 asersi), `php vendor/bin/pint --test` lulus, migrasi lokal dijalankan.
- 2026-10-26 · T-603, T-606 · Ditambahkan indeks untuk daftar dan agregasi umum, serta uji dashboard berisi 500 mahasiswa yang membatasi jumlah query; ditulis panduan pengguna admin/mahasiswa dan panduan persiapan rilis. Pengujian beban ini mengukur jumlah query, bukan waktu respons server produksi. T-602, T-604, T-605, dan T-607 tetap terbuka sampai uji browser desktop/ponsel, deployment, pemulihan backup staging, dan UAT benar-benar dijalankan. Backup terenkripsi harian dan perintah bootstrap admin sudah disiapkan, tetapi backup/restorasi produksi belum diverifikasi.
- 2026-10-26 · Revisi alur rekap pembinaan · Admin kini menetapkan status berdasarkan foto absensi yang sudah dikirim mahasiswa. Form koreksi tidak meminta unggah foto; koreksi mempertahankan foto dan waktu kirim asli. Persetujuan sebagai hadir ditolak jika tidak ada foto kiriman mahasiswa.
