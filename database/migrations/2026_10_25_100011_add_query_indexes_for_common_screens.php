<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->index('status_kipk', 'idx_mhs_status');
            $table->index(['prodi', 'angkatan'], 'idx_mhs_prodi_angkatan');
        });

        Schema::table('kegiatan_pembinaan', function (Blueprint $table) {
            $table->index('absen_tutup', 'idx_kegiatan_tutup');
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->index(['mahasiswa_id', 'status', 'kegiatan_id'], 'idx_absensi_mhs_status_kegiatan');
        });

        Schema::table('dokumentasi_mingguan', function (Blueprint $table) {
            $table->index(['status_verifikasi', 'created_at'], 'idx_dok_status_created');
        });

        Schema::table('peringatan', function (Blueprint $table) {
            $table->index(['tingkat', 'tanggal'], 'idx_peringatan_tingkat_tanggal');
        });

        Schema::table('notifikasi', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_notif_user_created');
        });

        Schema::table('audit_log', function (Blueprint $table) {
            $table->index('created_at', 'idx_audit_created');
            $table->index(['jenis_data', 'aksi', 'created_at'], 'idx_audit_type_action_date');
        });
    }

    public function down(): void
    {
        Schema::table('audit_log', function (Blueprint $table) {
            $table->dropIndex('idx_audit_created');
            $table->dropIndex('idx_audit_type_action_date');
        });

        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropIndex('idx_notif_user_created');
        });

        Schema::table('peringatan', function (Blueprint $table) {
            $table->dropIndex('idx_peringatan_tingkat_tanggal');
        });

        Schema::table('dokumentasi_mingguan', function (Blueprint $table) {
            $table->dropIndex('idx_dok_status_created');
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex('idx_absensi_mhs_status_kegiatan');
        });

        Schema::table('kegiatan_pembinaan', function (Blueprint $table) {
            $table->dropIndex('idx_kegiatan_tutup');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropIndex('idx_mhs_status');
            $table->dropIndex('idx_mhs_prodi_angkatan');
        });
    }
};
