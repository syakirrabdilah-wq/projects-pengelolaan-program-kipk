-- Skema database: Website Manajemen Penerima KIP-K
-- Target: MySQL 8.0+ / MariaDB 10.5+ (InnoDB, utf8mb4)

CREATE DATABASE IF NOT EXISTS db_kipk
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_kipk;

-- 1. Akun pengguna (admin kemahasiswaan dan mahasiswa)
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','mahasiswa') NOT NULL,
  aktif         TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Data penerima KIP-K
CREATE TABLE mahasiswa (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL UNIQUE,
  nim         VARCHAR(20)  NOT NULL UNIQUE,
  nama        VARCHAR(100) NOT NULL,
  prodi       VARCHAR(100) NOT NULL,
  angkatan    YEAR NOT NULL,
  status_kipk ENUM('aktif','cuti','lulus','dicabut','mengundurkan_diri')
              NOT NULL DEFAULT 'aktif',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_mhs_status (status_kipk),
  KEY idx_mhs_prodi_angkatan (prodi, angkatan),
  CONSTRAINT fk_mhs_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Nilai per semester (diinput dan diunggah admin; mahasiswa hanya melihat)
CREATE TABLE semester_akademik (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mahasiswa_id  INT UNSIGNED NOT NULL,
  semester_ke   TINYINT UNSIGNED NOT NULL,
  ips           DECIMAL(3,2) NOT NULL,
  ipk           DECIMAL(3,2) NOT NULL,
  sks           SMALLINT UNSIGNED NOT NULL,
  file_khs      VARCHAR(255) NULL,
  diinput_oleh  INT UNSIGNED NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_mhs_semester (mahasiswa_id, semester_ke),
  CONSTRAINT chk_ips CHECK (ips BETWEEN 0 AND 4),
  CONSTRAINT chk_ipk CHECK (ipk BETWEEN 0 AND 4),
  CONSTRAINT fk_sem_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_sem_admin FOREIGN KEY (diinput_oleh) REFERENCES users(id)
) ENGINE=InnoDB;

-- 4. Batas minimal prestasi (punya riwayat berlaku)
CREATE TABLE aturan_prestasi (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ipk_minimal   DECIMAL(3,2) NOT NULL,
  berlaku_mulai DATE NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 5. Peringatan otomatis saat IPK di bawah batas
--    tingkat = urutan semester berturut-turut di bawah batas; tingkat >= pengaturan.peringatan_batas_pencabutan ditandai pencabutan
CREATE TABLE peringatan (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mahasiswa_id  INT UNSIGNED NOT NULL,
  aturan_id     INT UNSIGNED NOT NULL,
  semester_id   INT UNSIGNED NOT NULL,
  tingkat       TINYINT UNSIGNED NOT NULL DEFAULT 1,
  tanggal       DATE NOT NULL,
  UNIQUE KEY uq_peringatan_semester (mahasiswa_id, semester_id),
  KEY idx_peringatan_tingkat_tanggal (tingkat, tanggal),
  CONSTRAINT fk_per_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_per_aturan FOREIGN KEY (aturan_id) REFERENCES aturan_prestasi(id),
  CONSTRAINT fk_per_sem FOREIGN KEY (semester_id) REFERENCES semester_akademik(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Jadwal kegiatan pembinaan
CREATE TABLE kegiatan_pembinaan (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul       VARCHAR(150) NOT NULL,
  tanggal     DATE NOT NULL,
  absen_buka  DATETIME NOT NULL,
  absen_tutup DATETIME NOT NULL,
  dibuat_oleh INT UNSIGNED NOT NULL,
  KEY idx_kegiatan_tutup (absen_tutup),
  CONSTRAINT chk_jam_absen CHECK (absen_tutup > absen_buka),
  CONSTRAINT fk_keg_admin FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
) ENGINE=InnoDB;

-- 7. Absensi pembinaan (satu kali per mahasiswa per kegiatan)
CREATE TABLE absensi (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kegiatan_id   INT UNSIGNED NOT NULL,
  mahasiswa_id  INT UNSIGNED NOT NULL,
  waktu         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status        ENUM('hadir','izin','alpa') NOT NULL DEFAULT 'hadir',
  file_foto     VARCHAR(255) NULL,
  UNIQUE KEY uq_absen (kegiatan_id, mahasiswa_id),
  KEY idx_absensi_mhs_status_kegiatan (mahasiswa_id, status, kegiatan_id),
  CONSTRAINT fk_abs_keg FOREIGN KEY (kegiatan_id) REFERENCES kegiatan_pembinaan(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_abs_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Legacy dokumentasi mingguan (tidak dipakai alur aplikasi sejak 2026-10-26;
--    tabel dipertahankan agar deployment lama tidak kehilangan data historis).
CREATE TABLE dokumentasi_mingguan (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mahasiswa_id       INT UNSIGNED NOT NULL,
  tahun              SMALLINT UNSIGNED NOT NULL,
  minggu_ke          TINYINT UNSIGNED NOT NULL,
  file_foto          VARCHAR(255) NOT NULL,
  keterangan         VARCHAR(255) NULL,
  status_verifikasi  ENUM('menunggu','diterima','ditolak') NOT NULL DEFAULT 'menunggu',
  catatan_admin      VARCHAR(255) NULL,
  diverifikasi_oleh  INT UNSIGNED NULL,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_dok_minggu (mahasiswa_id, tahun, minggu_ke),
  KEY idx_dok_status_created (status_verifikasi, created_at),
  CONSTRAINT fk_dok_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_dok_admin FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id)
) ENGINE=InnoDB;

-- 9. Notifikasi dalam aplikasi
CREATE TABLE notifikasi (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  isi          VARCHAR(255) NOT NULL,
  sudah_dibaca TINYINT(1) NOT NULL DEFAULT 0,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_notif_user (user_id, sudah_dibaca),
  KEY idx_notif_user_created (user_id, created_at),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Pengaturan yang diubah admin lewat dashboard
CREATE TABLE pengaturan (
  kunci       VARCHAR(50)  NOT NULL PRIMARY KEY,
  nilai       VARCHAR(255) NOT NULL,
  diubah_oleh INT UNSIGNED NULL,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_set_admin FOREIGN KEY (diubah_oleh) REFERENCES users(id)
) ENGINE=InnoDB;

-- 11. Audit perubahan nilai, status KIP-K, aturan prestasi, dan pengaturan
CREATE TABLE audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NULL,
  jenis_data  VARCHAR(64) NOT NULL,
  data_id     VARCHAR(64) NOT NULL,
  aksi        VARCHAR(20) NOT NULL,
  perubahan   JSON NOT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_data (jenis_data, data_id),
  KEY idx_audit_user_created (user_id, created_at),
  KEY idx_audit_created (created_at),
  KEY idx_audit_type_action_date (jenis_data, aksi, created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- Data awal: batas IPK minimal 3,00
INSERT INTO aturan_prestasi (ipk_minimal, berlaku_mulai) VALUES (3.00, '2026-01-01');

-- Nilai awal pengaturan (dapat diubah admin di dashboard)
INSERT INTO pengaturan (kunci, nilai) VALUES
  ('peringatan_batas_pencabutan', '3');

-- Contoh query: mahasiswa yang IPK terbarunya di bawah batas yang berlaku
-- SELECT m.nim, m.nama, s.semester_ke, s.ipk
-- FROM mahasiswa m
-- JOIN semester_akademik s ON s.mahasiswa_id = m.id
-- WHERE s.semester_ke = (SELECT MAX(semester_ke) FROM semester_akademik WHERE mahasiswa_id = m.id)
--   AND s.ipk < (SELECT ipk_minimal FROM aturan_prestasi
--                WHERE berlaku_mulai <= CURDATE() ORDER BY berlaku_mulai DESC LIMIT 1);

-- Catatan: tiga akun admin dibuat lewat aplikasi (password di-hash dengan bcrypt),
-- jangan menyimpan password asli di file SQL.
