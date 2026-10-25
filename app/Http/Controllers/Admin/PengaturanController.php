<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PengaturanRequest;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    public function edit()
    {
        $nilai = Pengaturan::query()
            ->whereIn('kunci', [
                'dokumentasi_tenggat_hari',
                'dokumentasi_tenggat_jam',
                'peringatan_batas_pencabutan',
            ])
            ->pluck('nilai', 'kunci');

        return view('admin.pengaturan.edit', [
            'pengaturan' => [
                'dokumentasi_tenggat_hari' => $nilai['dokumentasi_tenggat_hari'] ?? '7',
                'dokumentasi_tenggat_jam' => $nilai['dokumentasi_tenggat_jam'] ?? '23:59',
                'peringatan_batas_pencabutan' => $nilai['peringatan_batas_pencabutan'] ?? '3',
            ],
        ]);
    }

    public function update(PengaturanRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            foreach ($data as $kunci => $nilai) {
                Pengaturan::updateOrCreate(
                    ['kunci' => $kunci],
                    [
                        'nilai' => (string) $nilai,
                        'diubah_oleh' => $request->user()->id,
                        'updated_at' => now(),
                    ],
                );
            }
        });

        return redirect()->route('admin.pengaturan.edit')->with('sukses', 'Pengaturan berhasil disimpan.');
    }
}
