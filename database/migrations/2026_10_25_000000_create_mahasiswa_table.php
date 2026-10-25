<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nim', 20)->unique();
            $table->string('nama', 100);
            $table->string('prodi', 100);
            $table->unsignedSmallInteger('angkatan');
            $table->enum('status_kipk', ['aktif', 'cuti', 'lulus', 'dicabut', 'mengundurkan_diri'])
                ->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
