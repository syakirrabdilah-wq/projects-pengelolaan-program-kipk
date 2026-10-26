# PRD — Website Manajemen Penerima KIP-K

> Product Requirements Document. Dokumen ini adalah sumber kebenaran untuk **apa** yang dibangun.
> Untuk **bagaimana**, lihat `design.md` dan `techstack.md`.

| | |
|---|---|
| Status | Draft v1.1 (pertanyaan terbuka sudah dijawab pemilik proyek) |
| Pemilik | Syakir (Teknik Informatika) |
| Dokumen terkait | `techstack.md`, `design.md`, `rules.md`, `agents/plan.md`, `agents/task.md` |

---

## 1. Latar belakang

Bagian kemahasiswaan perlu memantau penerima KIP-K (Kartu Indonesia Pintar Kuliah) di kampus: perkembangan akademik dan kehadiran kegiatan pembinaan dengan foto bukti. Pencatatan manual (spreadsheet dan chat) menyulitkan pemantauan, mudah terlewat, dan tidak transparan bagi mahasiswa.

## 2. Tujuan

1. Memusatkan data penerima KIP-K dan perkembangan akademiknya dalam satu sistem.
2. Memberi peringatan otomatis ketika IPK mahasiswa di bawah batas minimal.
3. Mencatat kehadiran pembinaan secara online dan terverifikasi waktu.
4. Memakai foto yang mahasiswa kirim saat absensi sebagai bukti kehadiran kegiatan pembinaan.
5. Memberi mahasiswa akses transparan (hanya-lihat) ke data akademik dan statusnya sendiri.

## 3. Pengguna

| Peran | Deskripsi | Hak utama |
|---|---|---|
| Admin kemahasiswaan | Staf yang mengelola penerima | Kelola penuh seluruh data, input nilai dan KHS, kelola pembinaan, meninjau bukti foto absensi |
| Mahasiswa penerima | Penerima KIP-K | Hanya-lihat nilai dan status sendiri, absen pembinaan dengan foto bukti |

## 4. Ruang lingkup

### Termasuk (in scope)
- Autentikasi dengan dua peran.
- Manajemen data penerima (CRUD, status, impor Excel).
- Input IPS, IPK, SKS, dan unggah KHS **oleh admin**; mahasiswa hanya melihat.
- Aturan batas IPK minimal yang dapat diubah, dengan riwayat berlaku.
- Peringatan otomatis dan notifikasi dalam aplikasi.
- Kegiatan pembinaan, absensi online berbukti foto bertimestamp server, dan rekap kehadiran.
- Admin meninjau foto absensi yang mahasiswa kirim dan menetapkan status kehadiran.
- Dashboard untuk kedua peran dan laporan ekspor PDF/Excel.

### Di luar lingkup (out of scope) untuk rilis pertama
- Penyaluran dana (UKT dan biaya hidup).
- Pendaftaran dan seleksi calon penerima.
- Notifikasi WhatsApp/email (tahap lanjutan).
- Integrasi dengan sistem akademik kampus atau sistem resmi KIP-K.
- Aplikasi mobile native (cukup web responsif).
- Pengajuan keberatan atas nilai atau peringatan di dalam sistem (ditangani di luar sistem lewat pesan pribadi WhatsApp).
- Pengamanan absensi dengan kode sesi, QR, atau geolokasi; absensi memakai jendela waktu dan foto.
- Unggah dokumentasi mingguan terpisah dan antrean verifikasi dokumentasi; foto bukti diunggah saat absensi pembinaan.

## 5. User stories

### Admin
- **A1** Sebagai admin, saya ingin menambah, mengubah, dan mencari data penerima agar data selalu mutakhir.
- **A2** Sebagai admin, saya ingin mengimpor data penerima dari Excel agar tidak mengetik satu per satu.
- **A3** Sebagai admin, saya ingin menginput IPS, IPK, SKS, dan mengunggah KHS per semester agar nilai tercatat resmi.
- **A4** Sebagai admin, saya ingin mengubah batas IPK minimal tanpa mengubah kode agar kebijakan dapat berubah.
- **A5** Sebagai admin, saya ingin melihat daftar mahasiswa yang diberi peringatan agar bisa segera dibina.
- **A6** Sebagai admin, saya ingin membuat jadwal pembinaan dan membuka atau menutup absen agar kehadiran valid.
- **A8** Sebagai admin, saya ingin mengekspor laporan agar bisa diserahkan ke pimpinan.

### Mahasiswa
- **M1** Sebagai mahasiswa, saya ingin melihat IPS, IPK, SKS per semester dan KHS saya.
- **M2** Sebagai mahasiswa, saya ingin melihat batas minimal prestasi dan posisi IPK saya terhadapnya.
- **M3** Sebagai mahasiswa, saya ingin menerima peringatan otomatis bila IPK saya di bawah batas.
- **M4** Sebagai mahasiswa, saya ingin melakukan absensi pembinaan dengan foto kegiatan dan melihat riwayat kehadiran saya.

## 6. Kebutuhan fungsional

| ID | Kebutuhan | Prioritas | Story |
|---|---|---|---|
| FR-01 | Login dengan username dan password; sesi berbasis peran | Must | semua |
| FR-02 | CRUD data penerima, status (aktif/cuti/lulus/dicabut/mengundurkan_diri) | Must | A1 |
| FR-03 | Impor data penerima dari Excel/CSV dengan validasi baris | Should | A2 |
| FR-04 | Input IPS, IPK, SKS per semester dan unggah KHS (PDF) oleh admin | Must | A3 |
| FR-05 | Mahasiswa melihat nilai, grafik, dan KHS miliknya sendiri (read-only) | Must | M1 |
| FR-06 | Kelola aturan batas IPK minimal dengan tanggal berlaku | Must | A4, M2 |
| FR-07 | Peringatan otomatis saat IPK < batas yang berlaku. Tingkat = urutan semester berturut-turut yang IPK-nya di bawah batas; tingkat 1-2 berupa pesan peringatan evaluasi, tingkat 3 ke atas ditandai pencabutan dan diberitahukan ke admin | Must | M3, A5 |
| FR-08 | Notifikasi dalam aplikasi untuk peringatan IPK | Must | M3 |
| FR-09 | CRUD kegiatan pembinaan dengan jam buka dan tutup absen | Must | A6 |
| FR-10 | Absensi mahasiswa hanya saat sesi dibuka, satu kali per kegiatan, dan wajib menyertakan foto kegiatan | Must | M4 |
| FR-11 | Rekap kehadiran per mahasiswa dan per kegiatan | Must | A6, M4 |
| FR-15 | Dashboard admin (ringkasan, grafik, daftar bermasalah) | Must | A5 |
| FR-16 | Dashboard mahasiswa (IPK, status, banner peringatan, kehadiran) | Must | M1–M5 |
| FR-17 | Ekspor laporan PDF/Excel (nilai dan kehadiran) | Should | A8 |
| FR-18 | Manajemen akun: reset password dan nonaktifkan akun | Should | A1 |
| FR-19 | Audit log perubahan data penting | Could | — |
| FR-20 | Halaman Pengaturan admin: batas jumlah peringatan untuk pencabutan | Must | A5 |

## 7. Aturan bisnis

- **BR-01** Hanya admin yang dapat membuat dan mengubah data nilai dan KHS.
- **BR-02** Mahasiswa hanya dapat mengakses data miliknya sendiri.
- **BR-03** Batas IPK awal 3,00. Evaluasi memakai aturan dengan `berlaku_mulai` terbaru yang tidak melewati tanggal hari ini.
- **BR-04** Peringatan dibuat saat IPK semester yang disimpan < batas. Satu peringatan per mahasiswa per semester. Tingkat peringatan = urutan semester berturut-turut yang IPK-nya di bawah batas (berurutan). Rangkaian kembali ke nol bila IPK semester berikutnya memenuhi batas.
- **BR-05** Absensi mahasiswa hanya diterima di antara `absen_buka` dan `absen_tutup`, hanya sekali per kegiatan, dan wajib menyertakan foto JPG/PNG maksimal 5 MB. Waktu absensi dicatat server saat foto diterima. Admin meninjau foto yang sudah dikirim mahasiswa pada halaman rekap, lalu menyetujui atau menolak absensi dengan menetapkan status; admin tidak perlu mengunggah foto.
- **BR-07** KHS dan foto bersifat privat; hanya admin dan pemilik data yang boleh mengaksesnya.
- **BR-08** Tingkat 1 dan 2: mahasiswa menerima pesan peringatan evaluasi. Tingkat 3 ke atas (batas dapat diatur): mahasiswa ditandai untuk pencabutan biaya KIP-K dan semua admin diberi tahu. Perubahan status menjadi `dicabut` dilakukan admin dengan konfirmasi.
- **BR-09** Ada tiga akun admin dengan hak yang sama; setiap penginputan nilai dan koreksi status absensi dicatat atas nama admin yang melakukannya.

## 8. Kebutuhan non-fungsional

| Aspek | Target |
|---|---|
| Keamanan | Password di-hash (bcrypt), HTTPS di produksi, proteksi CSRF dan XSS, otorisasi per peran, perlindungan data pribadi (NIM, KHS) |
| Kinerja | Halaman utama dimuat < 3 detik pada koneksi seluler normal untuk ±500 mahasiswa |
| Responsif | Sisi mahasiswa nyaman di layar ≥ 360 px |
| Berkas | Foto absensi JPG/PNG maks. 5 MB; KHS PDF maks. 5 MB |
| Ketersediaan data | Backup database berkala (harian disarankan) |
| Kemudahan perawatan | Kode mengikuti `rules.md`; migrasi database terversi |

## 9. Metrik keberhasilan

- 100% data nilai penerima aktif tercatat per semester.
- Mahasiswa dengan IPK di bawah batas terdeteksi otomatis tanpa pengecekan manual.
- ≥ 90% absensi tercatat online pada kegiatan pembinaan.
- Admin dapat menyiapkan laporan rekap dalam < 5 menit.

## 10. Asumsi dan risiko

| Asumsi / risiko | Mitigasi |
|---|---|
| Foto absensi tidak membuktikan lokasi atau waktu pengambilan | Waktu server mencatat saat file diterima; absensi tetap dibatasi jendela waktu |
| Data pribadi bocor | File privat, otorisasi di controller, HTTPS, akses admin terbatas |
| Admin salah input nilai | Validasi rentang 0–4, konfirmasi sebelum simpan, riwayat perubahan |
| Mahasiswa tidak punya koneksi stabil | Ukuran foto dikompres di sisi klien/server |

## 11. Keputusan dan pertanyaan terbuka

### Keputusan pemilik proyek (2026-10-24)

| No | Topik | Keputusan |
|---|---|---|
| 1 | Tenggat dokumentasi mingguan | Fitur dokumentasi mingguan dihapus pada 2026-10-26; foto bukti diunggah saat absensi pembinaan |
| 2 | Pengamanan absensi | Diperbarui 2026-10-26: jendela waktu dan foto kegiatan wajib saat mahasiswa absen; admin meninjau foto yang dikirim dan menetapkan status tanpa mengunggah ulang; timestamp berasal dari waktu server saat menerima foto; tanpa kode sesi, QR, atau geolokasi |
| 3 | Aturan peringatan | IPK di bawah standar 1x dan 2x: pesan peringatan evaluasi. Pencabutan biaya KIP-K mulai kejadian ke-3 dan dikonfirmasi admin. Kejadian dihitung **berurutan** (semester berturut-turut) |
| 4 | Jumlah admin | Tiga admin |
| 5 | Keberatan nilai atau peringatan | Tidak ada fitur di sistem; mahasiswa menyampaikannya lewat pesan pribadi WhatsApp |
| 6 | Dokumentasi setelah tenggat mingguan | Tidak berlaku setelah fitur dokumentasi mingguan dihapus pada 2026-10-26 |

### Asumsi sementara (menunggu konfirmasi)

1. Kata "evakuasi" pada jawaban pemilik proyek dibaca sebagai "evaluasi".
2. Semester yang tidak punya data (misalnya cuti dan belum diinput) memutus rangkaian berurutan; admin menginput data semua semester.

### Pertanyaan terbuka
Konfirmasi asumsi 3 (lihat Q-08 di `agents/task.md`).
