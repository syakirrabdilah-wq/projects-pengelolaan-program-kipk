<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aturan_prestasi', function (Blueprint $table) {
            $table->id();
            $table->decimal('ipk_minimal', 3, 2);
            $table->date('berlaku_mulai')->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aturan_prestasi');
    }
};
