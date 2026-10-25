<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_pembinaan', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->date('tanggal');
            $table->dateTime('absen_buka');
            $table->dateTime('absen_tutup');
            $table->foreignId('dibuat_oleh')->constrained('users');
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE TRIGGER chk_kegiatan_jam_absen_insert BEFORE INSERT ON kegiatan_pembinaan WHEN NOT (NEW.absen_tutup > NEW.absen_buka) BEGIN SELECT RAISE(ABORT, 'absen_tutup harus setelah absen_buka'); END");
            DB::statement("CREATE TRIGGER chk_kegiatan_jam_absen_update BEFORE UPDATE OF absen_buka, absen_tutup ON kegiatan_pembinaan WHEN NOT (NEW.absen_tutup > NEW.absen_buka) BEGIN SELECT RAISE(ABORT, 'absen_tutup harus setelah absen_buka'); END");
        } else {
            DB::statement('ALTER TABLE kegiatan_pembinaan ADD CONSTRAINT chk_jam_absen CHECK (absen_tutup > absen_buka)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_pembinaan');
    }
};
