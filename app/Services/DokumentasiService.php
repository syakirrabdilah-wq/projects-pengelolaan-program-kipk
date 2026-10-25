<?php

namespace App\Services;

use App\Models\DokumentasiMingguan;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DokumentasiService
{
    public function simpan(Mahasiswa $mahasiswa, UploadedFile $foto, ?string $keterangan): DokumentasiMingguan
    {
        $waktu = now();
        $tahun = $waktu->isoWeekYear;
        $minggu = $waktu->isoWeek;
        $filePath = "private/dokumentasi/{$mahasiswa->id}/{$tahun}-{$minggu}.jpg";
        $jpeg = $this->keJpeg($foto);

        try {
            return DB::transaction(function () use ($mahasiswa, $tahun, $minggu, $filePath, $jpeg, $keterangan, $waktu) {
                $dokumentasi = DokumentasiMingguan::query()
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('tahun', $tahun)
                    ->where('minggu_ke', $minggu)
                    ->lockForUpdate()
                    ->first();

                if ($dokumentasi && $dokumentasi->status_verifikasi !== 'ditolak') {
                    throw new DokumentasiException('Anda sudah mengirim dokumentasi untuk minggu ini.');
                }

                if (! Storage::disk('local')->put($filePath, $jpeg)) {
                    throw new DokumentasiException('Foto gagal disimpan. Silakan coba lagi.');
                }

                $data = [
                    'file_foto' => $filePath,
                    'keterangan' => $keterangan,
                    'status_verifikasi' => 'menunggu',
                    'catatan_admin' => null,
                    'diverifikasi_oleh' => null,
                    'created_at' => $waktu,
                ];

                if ($dokumentasi) {
                    $dokumentasi->update($data);
                } else {
                    $dokumentasi = DokumentasiMingguan::create([
                        'mahasiswa_id' => $mahasiswa->id,
                        'tahun' => $tahun,
                        'minggu_ke' => $minggu,
                        ...$data,
                    ]);
                }

                return $dokumentasi->refresh();
            });
        } catch (QueryException $exception) {
            $sudahMengirim = DokumentasiMingguan::query()
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('tahun', $tahun)
                ->where('minggu_ke', $minggu)
                ->exists();

            if ($sudahMengirim) {
                throw new DokumentasiException('Anda sudah mengirim dokumentasi untuk minggu ini.', 0, $exception);
            }

            throw $exception;
        }
    }

    public function tandaiTerlambat(Collection $dokumentasi): Collection
    {
        $pengaturan = $this->pengaturanTenggat();

        return $dokumentasi->each(function (DokumentasiMingguan $item) use ($pengaturan) {
            $item->setAttribute('terlambat', $this->terlambat($item, $pengaturan));
        });
    }

    public function terlambat(DokumentasiMingguan $dokumentasi, ?array $pengaturan = null): bool
    {
        $pengaturan ??= $this->pengaturanTenggat();
        $dibuat = CarbonImmutable::instance($dokumentasi->created_at)->setTimezone(config('app.timezone'));
        $tenggat = $this->hitungTenggat($dibuat, $pengaturan);

        return $dibuat->greaterThan($tenggat);
    }

    public function tenggatMinggu(CarbonImmutable $tanggal): CarbonImmutable
    {
        return $this->hitungTenggat($tanggal, $this->pengaturanTenggat());
    }

    private function hitungTenggat(CarbonImmutable $tanggal, array $pengaturan): CarbonImmutable
    {
        [$jam, $menit] = array_map('intval', explode(':', $pengaturan['jam']));

        return $tanggal->startOfWeek(Carbon::MONDAY)
            ->addDays($pengaturan['hari'] - 1)
            ->setTime($jam, $menit);
    }

    private function pengaturanTenggat(): array
    {
        $pengaturan = Pengaturan::query()
            ->whereIn('kunci', ['dokumentasi_tenggat_hari', 'dokumentasi_tenggat_jam'])
            ->pluck('nilai', 'kunci');

        return [
            'hari' => (int) ($pengaturan['dokumentasi_tenggat_hari'] ?? 7),
            'jam' => $pengaturan['dokumentasi_tenggat_jam'] ?? '23:59',
        ];
    }

    private function keJpeg(UploadedFile $foto): string
    {
        $jalur = $foto->getRealPath();
        $dimensi = $jalur ? @getimagesize($jalur) : false;
        $lebarAsli = is_array($dimensi) ? (int) $dimensi[0] : 0;
        $tinggiAsli = is_array($dimensi) ? (int) $dimensi[1] : 0;
        $pikselMaksimum = (int) config('dokumentasi.piksel_maksimum');

        if ($lebarAsli < 1 || $tinggiAsli < 1 || $lebarAsli * $tinggiAsli > $pikselMaksimum) {
            throw new DokumentasiException('Resolusi foto terlalu besar atau berkas gambar tidak valid. Pilih foto dengan resolusi lebih rendah.');
        }

        try {
            $gambar = @imagecreatefromstring(file_get_contents($jalur));
        } catch (\Throwable) {
            $gambar = false;
        }

        if (! $gambar instanceof \GdImage) {
            throw new DokumentasiException('Berkas bukan gambar JPG atau PNG yang valid.');
        }

        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);
        $lebarMaksimum = (int) config('dokumentasi.lebar_maksimum');
        $hasil = $gambar;

        if ($lebar > $lebarMaksimum) {
            $lebarBaru = $lebarMaksimum;
            $tinggiBaru = (int) round($tinggi * ($lebarBaru / $lebar));
            $hasil = imagecreatetruecolor($lebarBaru, $tinggiBaru);
            $putih = imagecolorallocate($hasil, 255, 255, 255);
            imagefill($hasil, 0, 0, $putih);
            imagecopyresampled($hasil, $gambar, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);
            imagedestroy($gambar);
        }

        ob_start();
        $berhasil = imagejpeg($hasil, null, (int) config('dokumentasi.kualitas_jpeg'));
        $konten = ob_get_clean();
        imagedestroy($hasil);

        if (! $berhasil || ! is_string($konten)) {
            throw new DokumentasiException('Foto gagal diproses. Silakan coba lagi.');
        }

        return $konten;
    }
}
