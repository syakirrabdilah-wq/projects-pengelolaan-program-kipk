<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AbsensiService
{
    public function statusJendela(KegiatanPembinaan $kegiatan): array
    {
        $sekarang = now();
        $buka = $kegiatan->absen_buka;
        $tutup = $kegiatan->absen_tutup;

        if ($sekarang->lt($buka)) {
            return ['bisa_absen' => false, 'alasan' => 'Absen dibuka pukul '.$buka->format('H.i').'.'];
        }

        if ($sekarang->gt($tutup)) {
            return ['bisa_absen' => false, 'alasan' => 'Waktu absen telah ditutup pukul '.$tutup->format('H.i').'.'];
        }

        return ['bisa_absen' => true, 'alasan' => null];
    }

    public function absen(Mahasiswa $mahasiswa, KegiatanPembinaan $kegiatan, UploadedFile $foto): Absensi
    {
        $jendela = $this->statusJendela($kegiatan);

        if (! $jendela['bisa_absen']) {
            throw new AbsensiException($jendela['alasan']);
        }

        if (Absensi::query()
            ->where('kegiatan_id', $kegiatan->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->exists()) {
            throw new AbsensiException('Absensi untuk kegiatan ini sudah tercatat.');
        }

        $filePath = $this->simpanFoto($foto, $kegiatan, $mahasiswa);
        $tersimpan = false;

        try {
            $absensi = Absensi::create([
                'kegiatan_id' => $kegiatan->id,
                'mahasiswa_id' => $mahasiswa->id,
                'waktu' => now(config('app.timezone')),
                'status' => 'hadir',
                'file_foto' => $filePath,
            ]);

            $tersimpan = true;

            return $absensi;
        } catch (QueryException $exception) {
            $sudahTercatat = Absensi::query()
                ->where('kegiatan_id', $kegiatan->id)
                ->where('mahasiswa_id', $mahasiswa->id)
                ->exists();

            if ($sudahTercatat) {
                throw new AbsensiException('Absensi untuk kegiatan ini sudah tercatat.', 0, $exception);
            }

            throw $exception;
        } finally {
            if (! $tersimpan) {
                Storage::disk('local')->delete($filePath);
            }
        }
    }

    public function koreksi(Mahasiswa $mahasiswa, KegiatanPembinaan $kegiatan, string $status, UploadedFile $foto): Absensi
    {
        $filePath = $this->simpanFoto($foto, $kegiatan, $mahasiswa);
        $fileLama = null;

        try {
            $absensi = DB::transaction(function () use ($mahasiswa, $kegiatan, $status, $filePath, &$fileLama) {
                $absensi = Absensi::query()
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->lockForUpdate()
                    ->first() ?? new Absensi([
                        'kegiatan_id' => $kegiatan->id,
                        'mahasiswa_id' => $mahasiswa->id,
                    ]);

                $fileLama = $absensi->file_foto;
                $absensi->fill([
                    'status' => $status,
                    'waktu' => now(config('app.timezone')),
                    'file_foto' => $filePath,
                ])->save();

                return $absensi;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($filePath);
            throw $exception;
        }

        if ($fileLama && $fileLama !== $filePath) {
            Storage::disk('local')->delete($fileLama);
        }

        return $absensi;
    }

    private function simpanFoto(UploadedFile $foto, KegiatanPembinaan $kegiatan, Mahasiswa $mahasiswa): string
    {
        $filePath = $foto->store("private/absensi/{$kegiatan->id}/{$mahasiswa->id}", 'local');

        if (! is_string($filePath)) {
            throw new AbsensiException('Foto absensi gagal disimpan. Silakan coba lagi.');
        }

        return $filePath;
    }
}
