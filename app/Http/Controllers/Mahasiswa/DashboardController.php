<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\DashboardMahasiswaService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMahasiswaService $dashboardService)
    {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa, 404);

        return view('mahasiswa.dashboard', $dashboardService->data($mahasiswa));
    }
}
