<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\DokumentasiRequest;
use App\Models\DokumentasiMingguan;
use App\Services\DokumentasiException;
use App\Services\DokumentasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumentasiController extends Controller
{
    public function index(Request $request, DokumentasiService $dokumentasiService)
    {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa, 404);

        $dokumentasi = $dokumentasiService->tandaiTerlambat(
            $mahasiswa->dokumentasiMingguan()->latest('created_at')->get(),
        );
        $tahun = now()->isoWeekYear;
        $minggu = now()->isoWeek;
        $mingguIni = $mahasiswa->dokumentasiMingguan()
            ->where('tahun', $tahun)
            ->where('minggu_ke', $minggu)
            ->first();

        return view('mahasiswa.dokumentasi.index', [
            'dokumentasi' => $dokumentasi,
            'mingguIni' => $mingguIni,
            'tahunIni' => $tahun,
            'nomorMingguIni' => $minggu,
        ]);
    }

    public function store(DokumentasiRequest $request, DokumentasiService $dokumentasiService)
    {
        try {
            $dokumentasiService->simpan(
                $request->user()->mahasiswa,
                $request->file('file_foto'),
                $request->validated('keterangan'),
            );
        } catch (DokumentasiException $exception) {
            return back()->withErrors(['file_foto' => $exception->getMessage()]);
        }

        return redirect()->route('mahasiswa.dokumentasi.index')->with('sukses', 'Dokumentasi berhasil dikirim.');
    }

    public function foto(Request $request, DokumentasiMingguan $dokumentasi)
    {
        abort_unless($request->user()->mahasiswa?->id === $dokumentasi->mahasiswa_id, 404);

        return $this->responsFoto($dokumentasi);
    }

    private function responsFoto(DokumentasiMingguan $dokumentasi)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($dokumentasi->file_foto), 404);

        return response()->file($disk->path($dokumentasi->file_foto), [
            'Content-Type' => 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
