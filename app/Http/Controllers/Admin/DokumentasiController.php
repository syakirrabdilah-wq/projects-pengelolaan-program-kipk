<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifikasiDokumentasiRequest;
use App\Models\DokumentasiMingguan;
use App\Services\DokumentasiService;
use App\Services\NotifikasiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DokumentasiController extends Controller
{
    public function index(DokumentasiService $dokumentasiService)
    {
        $dokumentasi = DokumentasiMingguan::query()
            ->with('mahasiswa')
            ->where('status_verifikasi', 'menunggu')
            ->latest('created_at')
            ->paginate(20);
        $dokumentasi->setCollection($dokumentasiService->tandaiTerlambat($dokumentasi->getCollection()));

        return view('admin.dokumentasi.index', compact('dokumentasi'));
    }

    public function show(DokumentasiMingguan $dokumentasi, DokumentasiService $dokumentasiService)
    {
        $dokumentasi->load('mahasiswa');
        $dokumentasiService->tandaiTerlambat(collect([$dokumentasi]));

        return view('admin.dokumentasi.show', compact('dokumentasi'));
    }

    public function verifikasi(
        VerifikasiDokumentasiRequest $request,
        DokumentasiMingguan $dokumentasi,
        NotifikasiService $notifikasiService,
    ) {
        abort_unless($dokumentasi->status_verifikasi === 'menunggu', 404);

        DB::transaction(function () use ($request, $dokumentasi, $notifikasiService) {
            $dokumentasi->update([
                'status_verifikasi' => $request->validated('status_verifikasi'),
                'catatan_admin' => $request->validated('catatan_admin'),
                'diverifikasi_oleh' => $request->user()->id,
            ]);
            $notifikasiService->hasilVerifikasi($dokumentasi->fresh());
        });

        return redirect()->route('admin.dokumentasi.index')->with('sukses', 'Dokumentasi berhasil diverifikasi.');
    }

    public function foto(DokumentasiMingguan $dokumentasi)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($dokumentasi->file_foto), 404);

        return response()->file($disk->path($dokumentasi->file_foto), [
            'Content-Type' => 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
