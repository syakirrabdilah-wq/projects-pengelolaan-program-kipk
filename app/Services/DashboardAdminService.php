<?php

namespace App\Services;

use App\Models\AturanPrestasi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\Peringatan;
use Illuminate\Support\Facades\DB;

class DashboardAdminService
{
    public function ringkasan(): array
    {
        $mahasiswaAktif = Mahasiswa::query()->where('status_kipk', 'aktif');
        $batasPencabutan = (int) (Pengaturan::query()
            ->where('kunci', 'peringatan_batas_pencabutan')
            ->value('nilai') ?? 3);

        return [
            'total_penerima' => Mahasiswa::query()->count(),
            'penerima_aktif' => (clone $mahasiswaAktif)->count(),
            'dalam_peringatan' => Peringatan::query()->distinct('mahasiswa_id')->count('mahasiswa_id'),
            'memenuhi_syarat_pencabutan' => Peringatan::query()
                ->where('tingkat', '>=', $batasPencabutan)
                ->distinct('mahasiswa_id')
                ->count('mahasiswa_id'),
        ];
    }

    public function grafik(): array
    {
        $prodi = Mahasiswa::query()
            ->select('prodi', DB::raw('COUNT(*) AS jumlah'))
            ->groupBy('prodi')
            ->orderBy('prodi')
            ->get();
        $angkatan = Mahasiswa::query()
            ->select('angkatan', DB::raw('COUNT(*) AS jumlah'))
            ->groupBy('angkatan')
            ->orderBy('angkatan')
            ->get();

        return [
            'label_prodi' => $prodi->pluck('prodi')->values(),
            'jumlah_prodi' => $prodi->pluck('jumlah')->map(fn ($jumlah) => (int) $jumlah)->values(),
            'label_angkatan' => $angkatan->pluck('angkatan')->map(fn ($tahun) => (string) $tahun)->values(),
            'jumlah_angkatan' => $angkatan->pluck('jumlah')->map(fn ($jumlah) => (int) $jumlah)->values(),
        ];
    }

    public function mahasiswaBermasalah(): array
    {
        $masalah = [];
        $aturan = AturanPrestasi::query()
            ->whereDate('berlaku_mulai', '<=', today())
            ->orderByDesc('berlaku_mulai')
            ->first();

        if ($aturan) {
            $semesterTerakhir = DB::table('semester_akademik')
                ->select('mahasiswa_id', DB::raw('MAX(semester_ke) AS semester_ke'))
                ->groupBy('mahasiswa_id');
            $ipkRendah = DB::table('mahasiswa AS m')
                ->joinSub($semesterTerakhir, 'terakhir', fn ($join) => $join->on('terakhir.mahasiswa_id', '=', 'm.id'))
                ->join('semester_akademik AS s', function ($join) {
                    $join->on('s.mahasiswa_id', '=', 'terakhir.mahasiswa_id')
                        ->on('s.semester_ke', '=', 'terakhir.semester_ke');
                })
                ->where('m.status_kipk', 'aktif')
                ->where('s.ipk', '<', $aturan->ipk_minimal)
                ->select('m.id', 'm.nama', 'm.nim', 's.ipk')
                ->get();

            foreach ($ipkRendah as $item) {
                $masalah[$item->id] = $this->barisMasalah($item->id, $item->nama, $item->nim);
                $masalah[$item->id]['alasan'][] = 'IPK di bawah batas';
                $masalah[$item->id]['ipk'] = (float) $item->ipk;
            }
        }

        $kegiatanSelesai = KegiatanPembinaan::query()
            ->where('absen_tutup', '<=', now())
            ->select('id');
        $ambangKehadiran = (int) config('dashboard.ambang_kehadiran_rendah');
        $kehadiranRendah = DB::table('mahasiswa AS m')
            ->crossJoinSub($kegiatanSelesai, 'k')
            ->leftJoin('absensi AS a', function ($join) {
                $join->on('a.kegiatan_id', '=', 'k.id')
                    ->on('a.mahasiswa_id', '=', 'm.id')
                    ->where('a.status', '=', 'hadir');
            })
            ->where('m.status_kipk', 'aktif')
            ->groupBy('m.id', 'm.nama', 'm.nim')
            ->select('m.id', 'm.nama', 'm.nim')
            ->selectRaw('COUNT(DISTINCT k.id) AS kegiatan_selesai')
            ->selectRaw('COUNT(DISTINCT a.id) AS kegiatan_hadir')
            ->havingRaw('(COUNT(DISTINCT a.id) * 100.0 / COUNT(DISTINCT k.id)) < ?', [$ambangKehadiran])
            ->get();

        foreach ($kehadiranRendah as $item) {
            $masalah[$item->id] ??= $this->barisMasalah($item->id, $item->nama, $item->nim);
            $masalah[$item->id]['alasan'][] = 'Kehadiran rendah';
            $masalah[$item->id]['persentase_kehadiran'] = round($item->kegiatan_hadir / $item->kegiatan_selesai * 100, 1);
        }

        $daftar = array_values($masalah);
        usort($daftar, fn ($a, $b) => strcasecmp($a['nama'], $b['nama']));

        return $daftar;
    }

    private function barisMasalah(int $id, string $nama, string $nim): array
    {
        return [
            'id' => $id,
            'nama' => $nama,
            'nim' => $nim,
            'alasan' => [],
            'ipk' => null,
            'persentase_kehadiran' => null,
        ];
    }
}
