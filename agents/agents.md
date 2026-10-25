# AGENTS — Panduan untuk AI Agent

> Dibaca pertama kali oleh setiap agent yang bekerja di repositori ini (Claude Code, Codex, Cursor, dll.).

## 1. Konteks proyek

Website manajemen penerima KIP-K untuk bagian kemahasiswaan kampus. Dua peran: **admin kemahasiswaan** (tiga akun, hak sama) dan **mahasiswa penerima**. Admin menginput nilai (IPS, IPK, SKS, KHS); mahasiswa hanya melihat, melakukan absensi pembinaan, dan mengunggah dokumentasi mingguan. Sistem membuat peringatan otomatis bila IPK di bawah batas (awal 3,00) dengan tingkat berurutan (semester berturut-turut di bawah batas): 1-2 pesan peringatan evaluasi, tingkat 3 ke atas ditandai pencabutan.

Stack: Laravel + MySQL + Blade + Bootstrap 5 (lihat `techstack.md`).

## 2. Urutan membaca

1. `prd.md` — apa yang dibangun dan aturan bisnis (BR-xx).
2. `design.md` — arsitektur, otorisasi, logika layanan, rute, UI.
3. `rules.md` — standar kode dan Definition of Done.
4. `techstack.md` — teknologi dan perintah setup.
5. `database/kipk_schema.sql` — struktur database.
6. `agents/plan.md` lalu `agents/task.md` — fase dan tugas yang sedang dikerjakan.

## 3. Cara kerja

1. Buka `agents/task.md`, pilih tugas berstatus `[ ]` dengan urutan fase terawal yang dependensinya sudah selesai.
2. Tandai `[~]` (sedang dikerjakan) sebelum mulai.
3. Baca bagian terkait di `prd.md` dan `design.md`. Jika ada yang ambigu, **jangan menebak kebijakan**; catat di bagian "Catatan agent" di `task.md` dan tanyakan ke pemilik proyek.
4. Kerjakan pada cabang `feat/<nama>` sesuai `rules.md`.
5. Tulis tes, jalankan tes dan Pint.
6. Perbarui dokumen yang terdampak, lalu tandai `[x]` di `task.md` dengan catatan singkat.
7. Ajukan perubahan sebagai commit/PR kecil yang merujuk `T-xxx` dan `FR-xx`.

## 4. Perintah umum

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan test
./vendor/bin/pint
php artisan schedule:work     # uji penjadwal secara lokal
```

## 5. Batasan keras

- Jangan menambah fitur di luar `prd.md` (bagian "Di luar lingkup").
- Jangan membuat rute atau form yang memungkinkan mahasiswa mengubah nilai.
- Jangan mengambil `mahasiswa_id` dari input pada sisi mahasiswa.
- Jangan menaruh KHS/foto di folder publik.
- Jangan mengubah struktur tabel tanpa migrasi dan pencatatan di `design.md`.
- Jangan memasukkan rahasia, data nyata, atau `.env` ke repositori.
- Jangan mengarang aturan KIP-K; hanya pakai aturan di `prd.md`.
- Jangan mencabut status KIP-K secara otomatis; admin yang mengubah status setelah konfirmasi (sudah dikonfirmasi pemilik proyek).
- Absensi mensyaratkan jendela waktu dan foto; jangan menambah kode sesi, QR, atau geolokasi.

## 6. Peran agent (bila bekerja paralel)

| Agent | Cakupan | Tidak boleh |
|---|---|---|
| **Backend** | Migrasi, model, service, controller, policy, FormRequest, command terjadwal | Mengubah tampilan secara besar |
| **Frontend** | View Blade, komponen, CSS/JS, Chart.js, responsivitas | Mengubah logika bisnis atau skema |
| **Database** | Migrasi, seeder, indeks, kueri laporan | Mengubah aturan bisnis |
| **QA** | Tes unit/feature, tes otorisasi, uji alur manual | Memperbaiki kode produksi tanpa membuat tugas |
| **Docs** | Menjaga `prd.md`, `design.md`, `task.md` tetap sinkron | Mengubah kebutuhan tanpa persetujuan |

## 7. Serah terima antar agent

Saat menyelesaikan tugas yang dilanjutkan agent lain, tulis di "Catatan agent" pada `task.md`:
- apa yang selesai dan file yang disentuh,
- apa yang belum/diasumsikan,
- cara menjalankan tes terkait.

## 8. Format laporan ke pemilik proyek

Ringkas dan jujur:
- **Selesai:** daftar tugas `T-xxx`.
- **Perlu keputusan:** pertanyaan spesifik (bukan umum).
- **Risiko/masalah:** bila ada.
- **Berikutnya:** tugas yang akan diambil.

## 9. Pemeriksaan sebelum menyatakan selesai

- [ ] Tes lulus dan Pint bersih.
- [ ] Otorisasi diuji (mahasiswa A tidak bisa melihat data B).
- [ ] Tidak ada rute tulis nilai untuk mahasiswa.
- [ ] Dokumen dan `task.md` diperbarui.
