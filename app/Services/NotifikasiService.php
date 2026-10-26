<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Peringatan;
use App\Models\User;

class NotifikasiService
{
    public function peringatan(Peringatan $peringatan, int $batasPencabutan): void
    {
        $peringatan->loadMissing('mahasiswa.user');
        $mahasiswa = $peringatan->mahasiswa;
        $memenuhiBatas = $peringatan->tingkat >= $batasPencabutan;

        $isi = $memenuhiBatas
            ? "Peringatan IPK tingkat {$peringatan->tingkat}: Anda memenuhi syarat pencabutan biaya KIP-K. Admin akan meninjau status Anda."
            : "IPK Anda berada di bawah batas minimal pada semester {$peringatan->semester->semester_ke}. Harap lakukan evaluasi akademik.";

        $penerima = collect([$mahasiswa->user_id]);

        if ($memenuhiBatas) {
            $penerima = $penerima->merge(User::query()->where('role', 'admin')->pluck('id'));
        }

        $waktu = now();
        Notifikasi::insert($penerima->unique()->map(fn ($userId) => [
            'user_id' => $userId,
            'isi' => $isi,
            'sudah_dibaca' => false,
            'created_at' => $waktu,
        ])->all());
    }
}
