<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KegiatanPembinaanRequest;
use App\Http\Requests\Admin\KoreksiAbsensiRequest;
use App\Models\Absensi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Services\AbsensiException;
use App\Services\AbsensiService;
use Illuminate\Support\Facades\Storage;

class KegiatanPembinaanController extends Controller
{
    public function index()
    {
        return view('admin.kegiatan.index', [
            'kegiatan' => KegiatanPembinaan::query()
                ->with('dibuatOleh')
                ->withCount('absensi')
                ->orderByDesc('tanggal')
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.kegiatan.create');
    }

    public function store(KegiatanPembinaanRequest $request)
    {
        KegiatanPembinaan::create([
            ...$request->validated(),
            'dibuat_oleh' => $request->user()->id,
        ]);

        return redirect()->route('admin.kegiatan.index')->with('sukses', 'Kegiatan pembinaan berhasil dibuat.');
    }

    public function show(KegiatanPembinaan $kegiatan)
    {
        return view('admin.kegiatan.show', [
            'kegiatan' => $kegiatan,
            'mahasiswa' => Mahasiswa::query()
                ->where('status_kipk', 'aktif')
                ->with(['absensi' => fn ($query) => $query->where('kegiatan_id', $kegiatan->id)])
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function edit(KegiatanPembinaan $kegiatan)
    {
        return view('admin.kegiatan.edit', compact('kegiatan'));
    }

    public function update(KegiatanPembinaanRequest $request, KegiatanPembinaan $kegiatan)
    {
        $kegiatan->update($request->validated());

        return redirect()->route('admin.kegiatan.show', $kegiatan)->with('sukses', 'Kegiatan pembinaan berhasil diperbarui.');
    }

    public function destroy(KegiatanPembinaan $kegiatan)
    {
        $kegiatan->delete();

        return redirect()->route('admin.kegiatan.index')->with('sukses', 'Kegiatan pembinaan berhasil dihapus.');
    }

    public function koreksiAbsensi(KoreksiAbsensiRequest $request, KegiatanPembinaan $kegiatan, Mahasiswa $mahasiswa, AbsensiService $absensiService)
    {
        try {
            $absensiService->koreksi($mahasiswa, $kegiatan, $request->validated('status'));
        } catch (AbsensiException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('sukses', 'Status absensi mahasiswa berhasil diperbarui.');
    }

    public function foto(Absensi $absensi)
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

    public function rekapMahasiswa(Mahasiswa $mahasiswa)
    {
        $kegiatan = KegiatanPembinaan::query()
            ->with(['absensi' => fn ($query) => $query->where('mahasiswa_id', $mahasiswa->id)])
            ->orderByDesc('tanggal')
            ->get();
        $selesai = $kegiatan->filter(fn ($item) => $item->absen_tutup->lte(now()));
        $hadir = $selesai->filter(fn ($item) => $item->absensi->contains(fn ($absensi) => $absensi->status === 'hadir'))->count();

        return view('admin.kegiatan.rekap-mahasiswa', [
            'mahasiswa' => $mahasiswa,
            'kegiatan' => $kegiatan,
            'persentase' => $selesai->isEmpty() ? 0 : round($hadir / $selesai->count() * 100, 1),
            'jumlahSelesai' => $selesai->count(),
        ]);
    }
}
