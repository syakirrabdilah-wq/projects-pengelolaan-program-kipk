<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('isi', 255);
            $table->boolean('sudah_dibaca')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'sudah_dibaca'], 'idx_notif_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
