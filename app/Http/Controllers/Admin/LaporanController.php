<?php

namespace App\Http\Controllers\Admin;

use App\Exports\KehadiranExport;
use App\Exports\NilaiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LaporanPdfRequest;
use App\Http\Requests\Admin\LaporanRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class LaporanController extends Controller
{
    public function index(LaporanRequest $request)
    {
        return view('admin.laporan.index', [
            'filter' => $request->validated(),
            'prodi' => DB::table('mahasiswa')->distinct()->orderBy('prodi')->pluck('prodi'),
            'angkatan' => DB::table('mahasiswa')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
            'semester' => DB::table('semester_akademik')->distinct()->orderBy('semester_ke')->pluck('semester_ke'),
        ]);
    }

    public function excel(string $jenis, LaporanRequest $request): BinaryFileResponse
    {
        $filters = $request->validated();
        $export = match ($jenis) {
            'nilai' => new NilaiExport($filters),
            'kehadiran' => new KehadiranExport($filters),
        };

        return Excel::download($export, "rekap-{$jenis}-".now()->format('Ymd-His').'.xlsx');
    }

    public function pdfSemester(LaporanPdfRequest $request): Response
    {
        $filter = $request->validated();
        $semester = (int) $filter['semester'];
        $data = DB::table('semester_akademik as s')
            ->join('mahasiswa as m', 'm.id', '=', 's.mahasiswa_id')
            ->where('s.semester_ke', $semester)
            ->when($filter['prodi'] ?? null, fn ($query, $prodi) => $query->where('m.prodi', $prodi))
            ->when($filter['angkatan'] ?? null, fn ($query, $angkatan) => $query->where('m.angkatan', $angkatan))
            ->select(['m.nim', 'm.nama', 'm.prodi', 'm.angkatan', 's.semester_ke', 's.ips', 's.ipk', 's.sks'])
            ->orderBy('m.prodi')
            ->orderBy('m.nama')
            ->get();

        return Pdf::loadView('admin.laporan.semester-pdf', [
            'namaInstansi' => config('laporan.nama_instansi'),
            'tanggalCetak' => now()->translatedFormat('d F Y H:i'),
            'semester' => $semester,
            'data' => $data,
            'filter' => $filter,
        ])->setPaper('a4', 'landscape')->download("laporan-semester-{$semester}-".now()->format('Ymd').'.pdf');
    }
}
