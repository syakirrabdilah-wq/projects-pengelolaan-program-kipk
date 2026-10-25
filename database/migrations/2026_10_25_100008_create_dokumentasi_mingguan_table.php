<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumentasi_mingguan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('minggu_ke');
            $table->string('file_foto', 255);
            $table->string('keterangan', 255)->nullable();
            $table->enum('status_verifikasi', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->string('catatan_admin', 255)->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['mahasiswa_id', 'tahun', 'minggu_ke'], 'uq_dok_minggu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumentasi_mingguan');
    }
};
