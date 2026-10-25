<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\DashboardMahasiswaService;
use App\Services\DokumentasiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMahasiswaService $dashboardService, DokumentasiService $dokumentasiService)
    {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa, 404);

        return view('mahasiswa.dashboard', $dashboardService->data($mahasiswa, $dokumentasiService));
    }
}
