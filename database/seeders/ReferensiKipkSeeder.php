<?php

namespace Database\Seeders;

use App\Models\AturanPrestasi;
use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class ReferensiKipkSeeder extends Seeder
{
    public function run(): void
    {
        AturanPrestasi::updateOrCreate(
            ['berlaku_mulai' => '2026-01-01'],
            ['ipk_minimal' => 3.00],
        );

        Pengaturan::updateOrCreate(
            ['kunci' => 'peringatan_batas_pencabutan'],
            ['nilai' => '3'],
        );

    }
}
