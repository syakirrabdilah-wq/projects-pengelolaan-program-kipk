# Flowchart Sistem Pengelolaan KIP-K

```mermaid
flowchart TD
    START([Mulai]) --> LOGIN[Masuk dengan username dan kata sandi]
    LOGIN --> VALID{Akun aktif dan kredensial valid?}
    VALID -- Tidak --> LOGIN
    VALID -- Ya --> ROLE{Peran pengguna}

    ROLE -- Admin --> ADASH[Dashboard admin]
    ROLE -- Mahasiswa --> MDASH[Dashboard mahasiswa]

    subgraph ADMIN[Alur admin]
        ADASH --> REC[Kelola penerima dan akun]
        ADASH --> RULE[Atur batas IPK dan tenggat dokumentasi]
        ADASH --> GRADE[Input nilai semester dan unggah KHS]
        GRADE --> EVAL[Sistem mengevaluasi IPK dengan aturan yang berlaku]
        EVAL --> BELOW{IPK di bawah batas?}
        BELOW -- Tidak --> SAVED[Nilai tersimpan]
        BELOW -- Ya --> WARN[Buat atau perbarui tingkat peringatan]
        WARN --> MNOTIF[Kirim notifikasi kepada mahasiswa]
        WARN --> THRESHOLD{Tingkat mencapai ambang pencabutan?}
        THRESHOLD -- Tidak --> SAVED
        THRESHOLD -- Ya --> ANOTIF[Beri tahu admin dan tandai perlu ditinjau]
        ANOTIF --> REVIEW[Admin meninjau dan mengonfirmasi]
        REVIEW --> REVOKE[Ubah status KIP-K bila disetujui]

        ADASH --> ACTIVITY[Buat jadwal dan jendela absensi]
        ACTIVITY --> ATTENDANCELOG[Lihat rekap absensi per kegiatan]
        ATTENDANCELOG --> CORRECT{Perlu koreksi absensi?}
        CORRECT -- Ya --> FIXATTEND[Admin memperbarui status dengan foto]
        FIXATTEND --> ATTENDOK
        CORRECT -- Tidak --> REPORT
        ADASH --> VERIFY[Tinjau dokumentasi mahasiswa]
        VERIFY --> DECISION{Keputusan verifikasi}
        DECISION -- Diterima --> ACCEPT[Kirim status diterima]
        DECISION -- Ditolak --> REJECT[Kirim catatan penolakan]
        ADASH --> REPORT[Lihat rekap dan ekspor laporan]
        ADASH --> AUDIT[Lihat audit perubahan data]
    end

    subgraph MAHASISWA[Alur mahasiswa]
        MDASH --> VIEW[Lihat nilai, KHS, status, dan peringatan milik sendiri]
        MDASH --> NOTIF[Lihat notifikasi dan tenggat]
        MDASH --> ATTEND[Memilih kegiatan pembinaan]
        ATTEND --> WINDOW{Jendela absensi sedang dibuka?}
        WINDOW -- Tidak --> WAIT[Menunggu jadwal absensi]
        WAIT --> ATTEND
        WINDOW -- Ya --> PHOTO[Unggah foto kegiatan]
        PHOTO --> STAMP[Sistem mencatat waktu dari server]
        STAMP --> ATTENDOK[Absensi tersimpan]

        MDASH --> DOC[Memeriksa status dokumentasi minggu ini]
        DOC --> ALLOWED{Belum mengirim atau kiriman ditolak?}
        ALLOWED -- Tidak --> NEXTWEEK[Menunggu minggu berikutnya]
        ALLOWED -- Ya --> UPLOAD[Unggah foto dan keterangan]
        UPLOAD --> CHECK[Validasi berkas dan batas satu kiriman per minggu]
        CHECK --> DEADLINE{Melewati tenggat?}
        DEADLINE -- Ya --> LATE[Simpan dan tandai terlambat]
        DEADLINE -- Tidak --> ONTIME[Simpan tepat waktu]
        LATE --> QUEUE[Masuk antrean verifikasi admin]
        ONTIME --> QUEUE
        QUEUE --> VERIFY
        REJECT --> RESUBMIT[Kirim ulang setelah membaca catatan]
        RESUBMIT --> UPLOAD
        ACCEPT --> DOCSTATUS[Status dokumentasi diperbarui]
    end

    subgraph OTOMATIS[Proses otomatis]
        SCHEDULE([Penjadwal harian]) --> REMIND{Belum mengirim dan mendekati tenggat?}
        REMIND -- Ya --> REMINDER[Kirim pengingat dokumentasi]
        REMIND -- Tidak --> NOREMIND[Tidak ada pengingat]
    end

    REMINDER --> NOTIF
    MNOTIF --> NOTIF
    ACCEPT --> NOTIF
    REJECT --> NOTIF
```

## Batas akses

- Admin mengelola data penerima, nilai, aturan, kegiatan, verifikasi, dan laporan.
- Mahasiswa hanya melihat data miliknya sendiri; mahasiswa tidak dapat mengubah nilai.
- KHS dan foto disimpan privat dan hanya dapat diakses oleh admin atau pemilik data.
- Pencabutan status KIP-K dilakukan admin secara manual setelah konfirmasi.
