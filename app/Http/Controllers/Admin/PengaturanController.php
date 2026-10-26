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
                'peringatan_batas_pencabutan',
            ])
            ->pluck('nilai', 'kunci');

        return view('admin.pengaturan.edit', [
            'pengaturan' => [
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
