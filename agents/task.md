# Task — Daftar Tugas

> Legenda status: `[ ]` belum · `[~]` dikerjakan · `[x]` selesai · `[!]` terblokir.
> Setiap tugas merujuk kebutuhan di `prd.md` (FR-xx / BR-xx). Aturan pengerjaan ada di `agents/agents.md` dan `rules.md`.

## Fase 0 — Persiapan

- [x] T-000 Rancang kebutuhan, ERD, dan skema SQL (`prd.md`, `database/kipk_schema.sql`)
- [x] T-001 Susun dokumen proyek (`prd.md`, `techstack.md`, `design.md`, `rules.md`, `agents/*`)
- [ ] T-002 Inisialisasi repositori Git, `.gitignore`, `.env.example`
- [ ] T-003 Pasang Laravel (tanpa starter kit), `maatwebsite/excel`, `dompdf`; cek versi PHP dan ekstensi
- [ ] T-004 Konfigurasi database lokal dan disk privat (`storage/app/private`)
- [ ] T-005 Buat migrasi dari `kipk_schema.sql` untuk 10 tabel (termasuk `pengaturan`)
- [ ] T-006 Seeder: aturan IPK awal 3,00, nilai awal tabel `pengaturan`, dan tiga akun admin (kata sandi dari `.env`; akun demo hanya untuk lokal)
- [ ] T-007 Layout dasar Blade admin dan mahasiswa (Bootstrap 5)

## Fase 1 — Autentikasi dan data penerima

- [ ] T-101 Login memakai `username` + middleware `EnsureRole` (FR-01)
- [ ] T-102 Pembatasan percobaan login dan halaman keluar
- [x] T-103 CRUD penerima + pembuatan akun mahasiswa otomatis (FR-02)
- [x] T-104 Pencarian, filter (prodi, angkatan, status), paginasi (FR-02)
- [x] T-105 Impor Excel/CSV dengan validasi baris dan laporan galat (FR-03)
- [ ] T-106 Manajemen akun untuk 3 admin dan mahasiswa: reset password, nonaktifkan (FR-18)
- [ ] T-107 Tes: rute admin tertutup untuk mahasiswa

## Fase 2 — Nilai akademik dan peringatan

- [x] T-201 Form nilai per semester: IPS, IPK, SKS (rentang 0–4) (FR-04, BR-01)
- [x] T-202 Unggah KHS PDF ke disk privat dan unduh lewat controller (FR-04, BR-07)
- [x] T-203 CRUD aturan prestasi dengan `berlaku_mulai` (FR-06)
- [x] T-204 `PeringatanService`: evaluasi, tingkat berurutan (reset saat IPK memenuhi batas), upsert, hapus dan nomor ulang bila nilai dikoreksi (FR-07, BR-03, BR-04, BR-08)
- [ ] T-205 `NotifikasiService` dan notifikasi peringatan (FR-08)
- [ ] T-206 Halaman "Nilai saya" read-only + grafik + unduh KHS sendiri (FR-05)
- [ ] T-207 Indikator posisi IPK terhadap batas di halaman mahasiswa (FR-06)
- [ ] T-208 Halaman admin Peringatan (FR-07)
- [x] T-209 Tes unit `PeringatanService` (di bawah, tepat di batas, di atas, tingkat 1-2-3 berurutan, reset saat memenuhi batas, semester tanpa data memutus rangkaian, koreksi nilai)
- [ ] T-210 Tes otorisasi: mahasiswa tidak bisa menulis nilai maupun melihat nilai orang lain (BR-01, BR-02)
- [ ] T-211 Halaman Pengaturan admin: hari dan jam tenggat, batas pencabutan (FR-20)
- [ ] T-212 Penanda pencabutan (tingkat di atas atau sama dengan batas), notifikasi ke semua admin, dan tombol Cabut KIP-K dengan konfirmasi (BR-08)

## Fase 3 — Pembinaan dan absensi

- [x] T-301 Keputusan: absensi hanya memakai jendela waktu (2026-10-24)
- [ ] T-302 CRUD kegiatan pembinaan dengan jam buka dan tutup (FR-09)
- [ ] T-303 `AbsensiService` + tombol absen mahasiswa (FR-10, BR-05)
- [ ] T-304 Rekap kehadiran per kegiatan dan per mahasiswa (FR-11)
- [ ] T-305 Koreksi status absensi oleh admin (hadir/izin/alpa)
- [ ] T-306 Tes: absen di luar jam ditolak; absen ganda ditolak

## Fase 4 — Dokumentasi mingguan

- [ ] T-401 Form unggah foto dengan pratinjau dan validasi JPG/PNG ≤ 5 MB (FR-12)
- [ ] T-402 `DokumentasiService`: tentukan minggu ISO, satu kiriman per minggu, kirim ulang bila ditolak (BR-06)
- [ ] T-403 Antrean verifikasi admin: terima/tolak + catatan (FR-13)
- [ ] T-404 Notifikasi hasil verifikasi (FR-08)
- [ ] T-405 Perintah terjadwal `KirimPengingatDokumentasi` (FR-14)
- [x] T-406 Keputusan: tenggat ditentukan admin lewat halaman Pengaturan (dikerjakan di T-211)
- [ ] T-407 Tes: kiriman ganda ditolak; akses foto orang lain ditolak
- [ ] T-408 Penandaan kiriman terlambat berdasarkan tenggat di Pengaturan (BR-06)

## Fase 5 — Dashboard, laporan, notifikasi

- [ ] T-501 Dashboard admin: kartu ringkasan, grafik, daftar bermasalah (FR-15)
- [ ] T-502 Dashboard mahasiswa: IPK, status, banner peringatan, kehadiran, status dokumentasi (FR-16)
- [ ] T-503 Ekspor Excel: rekap nilai, kehadiran, dokumentasi (FR-17)
- [ ] T-504 Ekspor PDF laporan semester (FR-17)
- [ ] T-505 Halaman notifikasi + tandai sudah dibaca (FR-08)
- [ ] T-506 Audit log perubahan data penting (FR-19, opsional)

## Fase 6 — Pengerasan dan rilis

- [ ] T-601 Tinjauan keamanan: otorisasi, unggahan, header, rate limit
- [ ] T-602 Uji alur lengkap di ponsel (≥ 360 px) dan desktop
- [ ] T-603 Optimasi kueri dan indeks dengan data simulasi ±500 mahasiswa
- [ ] T-604 Deploy: HTTPS, `APP_DEBUG=false`, cron penjadwal
- [ ] T-605 Backup harian database + folder privat, uji pemulihan
- [ ] T-606 Panduan singkat pengguna (admin dan mahasiswa)
- [ ] T-607 Uji pengguna dengan beberapa admin dan mahasiswa, catat masukan

## Keputusan dan pertanyaan

| ID | Topik | Terkait | Status |
|---|---|---|---|
| Q-01 | Tenggat dokumentasi mingguan | T-406 | Selesai: diatur admin di halaman Pengaturan |
| Q-02 | Pengamanan absensi | T-301 | Selesai: jendela waktu saja |
| Q-03 | Aturan tingkat peringatan | T-204 | Selesai: 1x dan 2x pesan peringatan evaluasi, pencabutan mulai kejadian ke-3 dan dikonfirmasi admin, dihitung berurutan |
| Q-04 | Jumlah admin | T-106 | Selesai: tiga admin |
| Q-05 | Keberatan nilai atau peringatan | PRD bagian 11 | Selesai: di luar sistem (WhatsApp pribadi) |
| Q-06 | Hitungan peringatan: kumulatif atau berurutan? | T-204 | Selesai: berurutan (2026-10-24) |
| Q-08 | Semester tanpa data (mis. cuti) memutus rangkaian berurutan; apakah perlu aturan khusus? | T-204 | _menunggu_ (asumsi: memutus rangkaian; admin menginput semua semester) |
| Q-07 | Dokumentasi yang dikirim lewat tenggat: diterima dan ditandai terlambat, atau ditolak? (jawaban terakhir belum memilih) | T-408 | _menunggu_ (asumsi: diterima, ditandai terlambat) |

## Catatan agent

> Tulis serah terima di sini: tanggal, `T-xxx`, apa yang selesai, file yang disentuh, asumsi, dan cara menjalankan tes.

- 2026-10-24 · T-000, T-001 · Dokumen awal dan skema SQL dibuat. Belum ada kode aplikasi. Mulai dari T-002.
- 2026-10-24 · PRD bagian 11 dijawab pemilik proyek. Dokumen, skema (tabel `pengaturan`), dan tugas disesuaikan. Asumsi sementara ada di Q-06 dan Q-07.
- 2026-10-24 · Dikonfirmasi: pencabutan mulai kejadian ke-3 dan dikonfirmasi admin (tidak otomatis). Q-06 tersisa soal kumulatif/berurutan; Q-07 belum terjawab jelas.
- 2026-10-24 · Dikonfirmasi: peringatan dihitung berurutan. Q-06 selesai. Tersisa Q-07 (dokumentasi lewat tenggat) dan Q-08 (semester tanpa data).
- 2026-10-25 · T-103–T-105 · CRUD penerima admin, akun mahasiswa otomatis (username = NIM), filter/paginasi, dan impor XLSX/XLS/CSV dengan validasi serta laporan per baris. File utama: `PenerimaController`, `PenerimaRequest`, `ImportPenerimaRequest`, `PenerimaImport`, model/migrasi `Mahasiswa`, rute dan Blade admin. Admin mengisi password awal minimal 8 karakter; impor memakai kolom `password` dan tidak menyimpan file. Status `dicabut` memerlukan konfirmasi di form atau kolom `konfirmasi_pencabutan` pada impor. Tes: `php artisan test`; Pint: `php vendor/bin/pint` pada file PHP yang berubah. Catatan: T-101 (login) dan bagian lain T-005 masih belum dikerjakan; akses rute memakai `auth` + `role:admin` dan tes otorisasi menggunakan sesi uji.
- 2026-10-25 · T-201, T-202, T-203, T-204, T-209 · Nilai admin dengan validasi dan pencatatan penginput, KHS PDF privat, CRUD aturan berlaku, sinkronisasi peringatan berurutan, dan tes unit/fitur. Migrasi yang ditambahkan: `semester_akademik`, `aturan_prestasi`, `peringatan`, `pengaturan`; `created_at` nilai menjadi tanggal evaluasi. Aturan historis tidak dapat dihapus; ambang pencabutan dibaca dari Pengaturan dan tidak mengubah status atau membuat notifikasi. KHS diunduh lewat controller admin/pemilik. Tes: `php artisan test`; Pint dijalankan pada seluruh PHP yang berubah. T-205 notifikasi belum dikerjakan; T-005 masih belum mencakup tabel lain dan T-101 login masih belum dikerjakan.
