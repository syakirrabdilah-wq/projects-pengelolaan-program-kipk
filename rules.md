# Rules — Aturan Pengerjaan Proyek

> Berlaku untuk semua kontributor, termasuk AI agent. Bila ada konflik, urutan prioritas: **keamanan > `prd.md` > `design.md` > `rules.md` > preferensi pribadi**.

## 1. Prinsip umum

1. Kerjakan sesuai `prd.md`. Fitur di luar lingkup tidak dibangun tanpa persetujuan pemilik proyek.
2. Perubahan kecil dan sering lebih baik daripada perubahan besar yang jarang.
3. Jika dokumen dan kode berbeda, perbaiki salah satunya di commit yang sama.
4. Jangan mengarang kebijakan KIP-K. Aturan bisnis hanya dari `prd.md`; yang belum jelas dicatat sebagai pertanyaan terbuka.

## 2. Bahasa

- **Antarmuka dan pesan galat:** Bahasa Indonesia.
- **Dokumen proyek:** Bahasa Indonesia.
- **Kode (nama kelas, fungsi, variabel, kolom DB):** mengikuti skema yang sudah ada (campuran Indonesia seperti `mahasiswa`, `semester_akademik`); konsisten, jangan mencampur dua gaya untuk konsep yang sama.
- **Pesan commit:** Bahasa Indonesia atau Inggris, pilih satu dan konsisten.

## 3. Standar kode (PHP/Laravel)

- Ikuti PSR-12; format dengan Laravel Pint sebelum commit.
- Nama kelas `PascalCase`, metode dan variabel `camelCase`, tabel/kolom `snake_case`.
- Controller tipis; logika bisnis di `app/Services`.
- Validasi lewat FormRequest; otorisasi lewat Policy/middleware.
- Gunakan Eloquent atau query builder dengan binding. **Dilarang** menyusun SQL dengan string konkatenasi dari input.
- Tidak ada logika di Blade selain tampilan sederhana.
- Tidak ada nilai ajaib (angka batas, path) di kode; pakai config atau tabel (`aturan_prestasi`).
- Komentar menjelaskan *mengapa*, bukan *apa*.

## 4. Database

- Semua perubahan struktur lewat **migrasi** Laravel; jangan mengubah tabel manual di server.
- Struktur harus tetap sejalan dengan `database/kipk_schema.sql`; penyimpangan dicatat di `design.md`.
- Pertahankan constraint: foreign key, unique, dan check yang sudah didefinisikan.
- Seeder hanya berisi data awal yang aman (aturan IPK awal, akun demo untuk lingkungan lokal).
- Jangan menyimpan password asli di seeder produksi.

## 5. Keamanan (wajib)

- Sisi mahasiswa: `mahasiswa_id` selalu dari sesi login, **tidak pernah** dari input.
- Mahasiswa tidak boleh menulis ke `semester_akademik` (BR-01). Pastikan tidak ada rute atau form untuk itu.
- File KHS dan foto disimpan di disk privat dan disajikan lewat controller yang memeriksa hak akses.
- Validasi unggahan: tipe, ukuran, dan nama file dibuat sistem.
- `.env`, kunci, dan data nyata tidak boleh masuk repositori.
- Jangan mencatat data pribadi (NIM lengkap, isi KHS) ke log.
- Perbarui dependensi yang memiliki celah keamanan diketahui.

## 6. Antarmuka

- Pesan galat jelas dan berada di dekat kolom yang bermasalah.
- Aksi yang mengubah data penting (simpan nilai, hapus) meminta konfirmasi.
- Setiap daftar punya keadaan kosong; setiap tombol yang dinonaktifkan menjelaskan alasannya.
- Uji tampilan di lebar 360 px untuk halaman mahasiswa.
- Status tidak hanya dibedakan lewat warna.

## 7. Git

- Cabang: `main` (stabil), `develop` (integrasi), `feat/<nama>`, `fix/<nama>`.
- Commit kecil dan bermakna, format: `jenis: ringkasan` (mis. `feat: tambah absensi pembinaan`). Jenis: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`.
- Jangan commit langsung ke `main`.
- Satu pull request = satu fitur/perbaikan dan merujuk ID tugas (`T-xxx`) dan kebutuhan (`FR-xx`).

## 8. Pengujian

- Setiap aturan bisnis (BR-xx) yang berdampak pada data memiliki minimal satu tes.
- Wajib ada tes otorisasi: mahasiswa A tidak bisa mengakses data mahasiswa B.
- Tes harus lulus sebelum merge.

## 9. Definition of Done

Sebuah tugas selesai bila:
- [ ] Sesuai kebutuhan di `prd.md` dan kriteria penerimaan tugas.
- [ ] Validasi, otorisasi, dan penanganan galat sudah ada.
- [ ] Tes relevan ditulis dan lulus.
- [ ] Kode terformat (Pint) dan tanpa peringatan yang jelas.
- [ ] Tampilan dicek di ponsel dan desktop (bila ada UI).
- [ ] Dokumen terkait diperbarui (`design.md`, `agents/task.md`).
- [ ] Tidak ada rahasia atau data nyata di commit.

## 10. Hal yang dilarang

- Menambah fitur di luar `prd.md` tanpa persetujuan.
- Menonaktifkan validasi atau otorisasi demi "cepat jalan".
- Menyimpan berkas pribadi di folder publik.
- Mengubah skema tanpa migrasi.
- Mengabaikan tes yang gagal.
