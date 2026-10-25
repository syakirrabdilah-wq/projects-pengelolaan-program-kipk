<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'jenis_data' => ['nullable', Rule::in(['semester_akademik', 'mahasiswa', 'aturan_prestasi', 'pengaturan'])],
            'aksi' => ['nullable', Rule::in(['dibuat', 'diubah', 'dihapus'])],
            'aktor' => ['nullable', 'string', 'max:50'],
            'data_id' => ['nullable', 'string', 'max:64'],
            'tanggal_dari' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_dari'],
        ]);

        $logs = AuditLog::query()
            ->with('pengguna:id,username')
            ->when($filter['jenis_data'] ?? null, fn ($query, $jenis) => $query->where('jenis_data', $jenis))
            ->when($filter['aksi'] ?? null, fn ($query, $aksi) => $query->where('aksi', $aksi))
            ->when($filter['aktor'] ?? null, fn ($query, $aktor) => $query->whereHas('pengguna', fn ($pengguna) => $pengguna->where('username', $aktor)))
            ->when($filter['data_id'] ?? null, fn ($query, $id) => $query->where('data_id', $id))
            ->when($filter['tanggal_dari'] ?? null, fn ($query, $tanggal) => $query->where(
                'created_at',
                '>=',
                CarbonImmutable::createFromFormat('Y-m-d', $tanggal, config('app.timezone'))->startOfDay(),
            ))
            ->when($filter['tanggal_sampai'] ?? null, fn ($query, $tanggal) => $query->where(
                'created_at',
                '<',
                CarbonImmutable::createFromFormat('Y-m-d', $tanggal, config('app.timezone'))->addDay()->startOfDay(),
            ))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $adminList = User::query()
            ->where('role', 'admin')
            ->orderBy('username')
            ->get(['username']);

        return view('admin.audit-log.index', compact('logs', 'adminList', 'filter'));
    }
}
