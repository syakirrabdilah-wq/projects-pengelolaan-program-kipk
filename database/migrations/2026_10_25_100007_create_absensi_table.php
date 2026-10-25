<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan_pembinaan')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->dateTime('waktu')->useCurrent();
            $table->enum('status', ['hadir', 'izin', 'alpa'])->default('hadir');
            $table->unique(['kegiatan_id', 'mahasiswa_id'], 'uq_absen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
