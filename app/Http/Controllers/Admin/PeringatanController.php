<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CabutKipkRequest;
use App\Models\Mahasiswa;
use App\Models\Peringatan;
use App\Services\PeringatanService;

class PeringatanController extends Controller
{
    public function index(PeringatanService $peringatanService)
    {
        return view('admin.peringatan.index', [
            'peringatan' => Peringatan::query()
                ->with(['mahasiswa', 'semester'])
                ->orderBy('tingkat')
                ->orderByDesc('tanggal')
                ->paginate(25),
            'batasPencabutan' => $peringatanService->batasPencabutan(),
        ]);
    }

    public function cabut(CabutKipkRequest $request, Mahasiswa $mahasiswa, PeringatanService $peringatanService)
    {
        if ($mahasiswa->status_kipk === 'dicabut') {
            return back()->with('info', 'Status mahasiswa sudah dicabut.');
        }

        $layakDicabut = $mahasiswa->peringatan()
            ->where('tingkat', '>=', $peringatanService->batasPencabutan())
            ->exists();

        if (! $layakDicabut) {
            return back()->withErrors(['pencabutan' => 'Mahasiswa belum mencapai ambang pencabutan yang berlaku.']);
        }

        $mahasiswa->update(['status_kipk' => 'dicabut']);

        return redirect()->route('admin.peringatan.index')->with('sukses', 'Status KIP-K mahasiswa berhasil dicabut.');
    }
}
