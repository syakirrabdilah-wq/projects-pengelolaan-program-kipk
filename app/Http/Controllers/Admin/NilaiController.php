<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NilaiRequest;
use App\Models\Mahasiswa;
use App\Models\SemesterAkademik;
use App\Services\PeringatanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NilaiController extends Controller
{
    public function index(Mahasiswa $penerima)
    {
        return view('admin.nilai.index', [
            'penerima' => $penerima,
            'semester' => $penerima->semester()->with('diinputOleh')->orderBy('semester_ke')->paginate(15),
        ]);
    }

    public function create(Mahasiswa $penerima)
    {
        return view('admin.nilai.create', compact('penerima'));
    }

    public function store(NilaiRequest $request, Mahasiswa $penerima, PeringatanService $peringatanService)
    {
        $data = $request->validated();
        $path = $this->storeKhs($request, $penerima);

        try {
            DB::transaction(function () use ($data, $penerima, $path, $request, $peringatanService) {
                $penerima->semester()->create([
                    'semester_ke' => $data['semester_ke'],
                    'ips' => $data['ips'],
                    'ipk' => $data['ipk'],
                    'sks' => $data['sks'],
                    'file_khs' => $path,
                    'diinput_oleh' => $request->user()->id,
                    'created_at' => now(),
                ]);

                $peringatanService->evaluasi($penerima);
                // Hook T-205: pengiriman notifikasi dilakukan setelah sinkronisasi peringatan.
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()->route('admin.penerima.nilai.index', $penerima)->with('sukses', 'Nilai semester berhasil disimpan.');
    }

    public function edit(Mahasiswa $penerima, SemesterAkademik $semester)
    {
        $semester = $this->semesterMilik($penerima, $semester);

        return view('admin.nilai.edit', compact('penerima', 'semester'));
    }

    public function update(NilaiRequest $request, Mahasiswa $penerima, SemesterAkademik $semester, PeringatanService $peringatanService)
    {
        $semester = $this->semesterMilik($penerima, $semester);
        $data = $request->validated();
        $oldPath = $semester->file_khs;
        $newPath = $this->storeKhs($request, $penerima);

        try {
            DB::transaction(function () use ($data, $penerima, $semester, $newPath, $request, $peringatanService) {
                $semester->update([
                    'semester_ke' => $data['semester_ke'],
                    'ips' => $data['ips'],
                    'ipk' => $data['ipk'],
                    'sks' => $data['sks'],
                    'file_khs' => $newPath ?? $semester->file_khs,
                    'diinput_oleh' => $request->user()->id,
                ]);

                $peringatanService->evaluasi($penerima);
                // Hook T-205: pengiriman notifikasi dilakukan setelah sinkronisasi peringatan.
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('admin.penerima.nilai.index', $penerima)->with('sukses', 'Nilai semester berhasil diperbarui.');
    }

    public function downloadForAdmin(SemesterAkademik $semester)
    {
        return $this->download($semester);
    }

    public function downloadForMahasiswa(Request $request, SemesterAkademik $semester)
    {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa && (int) $semester->mahasiswa_id === (int) $mahasiswa->id, 404);

        return $this->download($semester);
    }

    private function semesterMilik(Mahasiswa $penerima, SemesterAkademik $semester): SemesterAkademik
    {
        return $penerima->semester()->whereKey($semester->id)->firstOrFail();
    }

    private function storeKhs(NilaiRequest $request, Mahasiswa $penerima): ?string
    {
        if (! $request->hasFile('file_khs')) {
            return null;
        }

        $path = $request->file('file_khs')->store("khs/{$penerima->id}", 'local');
        abort_unless(is_string($path), 500, 'Berkas KHS gagal disimpan.');

        return $path;
    }

    private function download(SemesterAkademik $semester)
    {
        abort_unless($semester->file_khs && Storage::disk('local')->exists($semester->file_khs), 404);

        return Storage::disk('local')->download(
            $semester->file_khs,
            "KHS-semester-{$semester->semester_ke}.pdf",
            ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
