<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AturanPrestasi;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa, 404);

        $semester = $mahasiswa->semester()->orderBy('semester_ke')->get();
        $ipkTerakhir = $semester->last()?->ipk;
        $aturan = AturanPrestasi::query()
            ->whereDate('berlaku_mulai', '<=', today())
            ->orderByDesc('berlaku_mulai')
            ->first();
        $batas = $aturan?->ipk_minimal;

        $posisi = match (true) {
            $ipkTerakhir === null => 'Nilai IPK belum tersedia.',
            $batas === null => 'Batas IPK belum tersedia.',
            (float) $ipkTerakhir < (float) $batas => "IPK terakhir {$ipkTerakhir} berada di bawah batas minimal {$batas}.",
            default => "IPK terakhir {$ipkTerakhir} memenuhi batas minimal {$batas}.",
        };

        return view('mahasiswa.nilai.index', [
            'semester' => $semester,
            'ipkTerakhir' => $ipkTerakhir,
            'batas' => $batas,
            'posisi' => $posisi,
        ]);
    }
}
