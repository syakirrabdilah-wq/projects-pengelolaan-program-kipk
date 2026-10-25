<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_ke');
            $table->decimal('ips', 3, 2);
            $table->decimal('ipk', 3, 2);
            $table->unsignedSmallInteger('sks');
            $table->string('file_khs', 255)->nullable();
            $table->foreignId('diinput_oleh')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['mahasiswa_id', 'semester_ke'], 'uq_mhs_semester');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE semester_akademik ADD CONSTRAINT chk_ips CHECK (ips BETWEEN 0 AND 4)');
            DB::statement('ALTER TABLE semester_akademik ADD CONSTRAINT chk_ipk CHECK (ipk BETWEEN 0 AND 4)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_akademik');
    }
};
