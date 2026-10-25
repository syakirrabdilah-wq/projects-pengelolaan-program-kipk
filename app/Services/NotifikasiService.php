<?php

namespace App\Services;

use App\Models\DokumentasiMingguan;
use App\Models\Notifikasi;
use App\Models\Peringatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotifikasiService
{
    public function hasilVerifikasi(DokumentasiMingguan $dokumentasi): void
    {
        $dokumentasi->loadMissing('mahasiswa');
        $isi = $dokumentasi->status_verifikasi === 'diterima'
            ? "Dokumentasi minggu ke-{$dokumentasi->minggu_ke} ({$dokumentasi->tahun}) diterima."
            : 'Dokumentasi ditolak.'.($dokumentasi->catatan_admin ? " Catatan: {$dokumentasi->catatan_admin}" : '');

        Notifikasi::create([
            'user_id' => $dokumentasi->mahasiswa->user_id,
            'isi' => $isi,
            'sudah_dibaca' => false,
            'created_at' => now(),
        ]);
    }

    public function pengingatDokumentasi(User $user, int $tahun, int $minggu): bool
    {
        $isi = "Jangan lupa mengirim dokumentasi minggu ke-{$minggu} tahun {$tahun} sebelum tenggat.";
        $hariIni = now()->toDateString();

        return DB::transaction(function () use ($user, $isi, $hariIni) {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (Notifikasi::query()
                ->where('user_id', $user->id)
                ->where('isi', $isi)
                ->whereDate('created_at', $hariIni)
                ->exists()) {
                return false;
            }

            Notifikasi::create([
                'user_id' => $user->id,
                'isi' => $isi,
                'sudah_dibaca' => false,
                'created_at' => now(),
            ]);

            return true;
        });
    }

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
