<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peringatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('aturan_id')->constrained('aturan_prestasi');
            $table->foreignId('semester_id')->constrained('semester_akademik')->cascadeOnDelete();
            $table->unsignedTinyInteger('tingkat')->default(1);
            $table->date('tanggal');
            $table->unique(['mahasiswa_id', 'semester_id'], 'uq_peringatan_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peringatan');
    }
};
