<?php

namespace App\Services;

use App\Models\AturanPrestasi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DashboardMahasiswaService
{
    public function data(Mahasiswa $mahasiswa): array
    {
        $semesterTerakhir = $mahasiswa->semester()->orderByDesc('semester_ke')->first();
        $aturan = AturanPrestasi::query()
            ->whereDate('berlaku_mulai', '<=', today())
            ->orderByDesc('berlaku_mulai')
            ->first();
        $peringatan = $mahasiswa->peringatan()->with(['semester', 'aturan'])->latest('tanggal')->first();
        $sekarang = CarbonImmutable::now(config('app.timezone'));
        $kegiatanSelesai = KegiatanPembinaan::query()->where('absen_tutup', '<=', $sekarang)->select('id');
        $jumlahKegiatan = (clone $kegiatanSelesai)->count();
        $jumlahHadir = $jumlahKegiatan === 0 ? 0 : DB::table('absensi')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'hadir')
            ->whereIn('kegiatan_id', $kegiatanSelesai)
            ->count();
        return [
            'mahasiswa' => $mahasiswa,
            'semesterTerakhir' => $semesterTerakhir,
            'aturan' => $aturan,
            'peringatan' => $peringatan,
            'batasPencabutan' => (int) (Pengaturan::query()
                ->where('kunci', 'peringatan_batas_pencabutan')
                ->value('nilai') ?? 3),
            'jumlahKegiatanSelesai' => $jumlahKegiatan,
            'jumlahHadir' => $jumlahHadir,
            'persentaseKehadiran' => $jumlahKegiatan === 0 ? null : round($jumlahHadir / $jumlahKegiatan * 100, 1),
        ];
    }
}
