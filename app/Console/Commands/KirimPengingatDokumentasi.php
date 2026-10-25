<?php

namespace App\Console\Commands;

use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Services\NotifikasiService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class KirimPengingatDokumentasi extends Command
{
    protected $signature = 'kipk:dokumentasi-pengingat';

    protected $description = 'Kirim pengingat dokumentasi pada hari tenggat mingguan.';

    public function handle(NotifikasiService $notifikasiService): int
    {
        $pengaturan = Pengaturan::query()
            ->whereIn('kunci', ['dokumentasi_tenggat_hari', 'dokumentasi_tenggat_jam'])
            ->pluck('nilai', 'kunci');
        $hariTenggat = (int) ($pengaturan['dokumentasi_tenggat_hari'] ?? 7);
        $jamTenggat = $pengaturan['dokumentasi_tenggat_jam'] ?? '23:59';
        $sekarang = CarbonImmutable::now(config('app.timezone'));

        if ($sekarang->isoWeekday() !== $hariTenggat) {
            $this->components->info('Hari ini bukan hari tenggat dokumentasi.');

            return self::SUCCESS;
        }

        [$jam, $menit] = array_map('intval', explode(':', $jamTenggat));
        if ($sekarang->greaterThanOrEqualTo($sekarang->setTime($jam, $menit))) {
            $this->components->info('Tenggat dokumentasi minggu ini sudah tiba.');

            return self::SUCCESS;
        }

        $tahun = $sekarang->isoWeekYear;
        $minggu = $sekarang->isoWeek;
        $jumlah = 0;

        Mahasiswa::query()
            ->where('status_kipk', 'aktif')
            ->whereHas('user', fn ($query) => $query->where('aktif', true))
            ->whereDoesntHave('dokumentasiMingguan', fn ($query) => $query
                ->where('tahun', $tahun)
                ->where('minggu_ke', $minggu))
            ->with('user')
            ->chunkById(100, function ($mahasiswaList) use ($notifikasiService, $tahun, $minggu, &$jumlah) {
                foreach ($mahasiswaList as $mahasiswa) {
                    if ($notifikasiService->pengingatDokumentasi($mahasiswa->user, $tahun, $minggu)) {
                        $jumlah++;
                    }
                }
            });

        $this->components->info("Pengingat dokumentasi dikirim: {$jumlah}.");

        return self::SUCCESS;
    }
}
