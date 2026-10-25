<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardAdminService;

class DashboardController extends Controller
{
    public function index(DashboardAdminService $dashboardService)
    {
        return view('admin.dashboard', [
            'ringkasan' => $dashboardService->ringkasan(),
            'grafik' => $dashboardService->grafik(),
            'mahasiswaBermasalah' => $dashboardService->mahasiswaBermasalah(),
            'ambangKehadiranRendah' => config('dashboard.ambang_kehadiran_rendah'),
        ]);
    }
}
