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
        ADASH --> RULE[Atur batas IPK dan ambang pencabutan]
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
        CORRECT -- Ya --> FIXATTEND[Admin meninjau foto kiriman mahasiswa dan menyetujui atau menolak absensi]
        FIXATTEND --> ATTENDOK
        CORRECT -- Tidak --> REPORT
        ADASH --> REPORT[Lihat rekap dan ekspor laporan]
        ADASH --> AUDIT[Lihat audit perubahan data]
    end

    subgraph MAHASISWA[Alur mahasiswa]
        MDASH --> VIEW[Lihat nilai, KHS, status, dan peringatan milik sendiri]
        MDASH --> NOTIF[Lihat notifikasi]
        MDASH --> ATTEND[Memilih kegiatan pembinaan]
        ATTEND --> WINDOW{Jendela absensi sedang dibuka?}
        WINDOW -- Tidak --> WAIT[Menunggu jadwal absensi]
        WAIT --> ATTEND
        WINDOW -- Ya --> PHOTO[Unggah foto kegiatan]
        PHOTO --> STAMP[Sistem mencatat waktu dari server]
        STAMP --> ATTENDOK[Absensi tersimpan]

    end

    MNOTIF --> NOTIF
```

## Batas akses

- Admin mengelola data penerima, nilai, aturan, kegiatan, status absensi, dan laporan.
- Mahasiswa hanya melihat data miliknya sendiri; mahasiswa tidak dapat mengubah nilai.
- KHS dan foto bukti absensi disimpan privat dan hanya dapat diakses oleh admin atau pemilik data.
- Pencabutan status KIP-K dilakukan admin secara manual setelah konfirmasi.
