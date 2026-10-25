<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\AbsensiRequest;
use App\Models\Absensi;
use App\Models\KegiatanPembinaan;
use App\Services\AbsensiException;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembinaanController extends Controller
{
    public function index(Request $request, AbsensiService $absensiService)
    {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa, 404);

        $kegiatan = KegiatanPembinaan::query()
            ->with(['absensi' => fn ($query) => $query->where('mahasiswa_id', $mahasiswa->id)])
            ->orderByDesc('tanggal')
            ->get();
        $selesai = $kegiatan->filter(fn ($item) => $item->absen_tutup->lte(now()));
        $hadir = $selesai->filter(fn ($item) => $item->absensi->contains(fn ($absensi) => $absensi->status === 'hadir'))->count();

        $statusJendela = $kegiatan->mapWithKeys(fn ($item) => [
            $item->id => $absensiService->statusJendela($item),
        ]);

        return view('mahasiswa.pembinaan.index', [
            'kegiatan' => $kegiatan,
            'persentase' => $selesai->isEmpty() ? 0 : round($hadir / $selesai->count() * 100, 1),
            'jumlahSelesai' => $selesai->count(),
            'statusJendela' => $statusJendela,
        ]);
    }

    public function absen(AbsensiRequest $request, KegiatanPembinaan $kegiatan, AbsensiService $absensiService)
    {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa, 404);

        try {
            $absensiService->absen($mahasiswa, $kegiatan, $request->file('file_foto'));
        } catch (AbsensiException $exception) {
            return back()->withErrors(['absensi' => $exception->getMessage()]);
        }

        return back()->with('sukses', 'Absensi kegiatan berhasil dicatat.');
    }

    public function foto(Request $request, Absensi $absensi)
    {
        abort_unless((int) $request->user()->mahasiswa?->id === (int) $absensi->mahasiswa_id, 404);

        return $this->responsFoto($absensi);
    }

    private function responsFoto(Absensi $absensi)
    {
        $filePath = $absensi->file_foto;
        $disk = Storage::disk('local');
        abort_unless(is_string($filePath) && $disk->exists($filePath), 404);

        return response()->file($disk->path($filePath), [
            'Content-Type' => $disk->mimeType($filePath) ?? 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
