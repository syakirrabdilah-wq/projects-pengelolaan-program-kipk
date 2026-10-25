<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AturanPrestasiRequest;
use App\Models\AturanPrestasi;
use Illuminate\Support\Carbon;

class AturanPrestasiController extends Controller
{
    public function index()
    {
        return view('admin.aturan-prestasi.index', [
            'aturan' => AturanPrestasi::query()->orderByDesc('berlaku_mulai')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.aturan-prestasi.create');
    }

    public function store(AturanPrestasiRequest $request)
    {
        AturanPrestasi::create($request->validated());

        return redirect()->route('admin.aturan-prestasi.index')->with('sukses', 'Aturan prestasi berhasil ditambahkan.');
    }

    public function edit(AturanPrestasi $aturanPrestasi)
    {
        return view('admin.aturan-prestasi.edit', compact('aturanPrestasi'));
    }

    public function update(AturanPrestasiRequest $request, AturanPrestasi $aturanPrestasi)
    {
        $aturanPrestasi->update($request->validated());

        return redirect()->route('admin.aturan-prestasi.index')->with('sukses', 'Aturan prestasi berhasil diperbarui.');
    }

    public function destroy(AturanPrestasi $aturanPrestasi)
    {
        $sudahBerlaku = $aturanPrestasi->berlaku_mulai->lte(Carbon::today());
        $sudahDipakai = $aturanPrestasi->peringatan()->exists();

        if ($sudahBerlaku || $sudahDipakai) {
            return back()->withErrors([
                'aturan' => 'Aturan yang sudah berlaku atau menjadi dasar peringatan tidak dapat dihapus agar riwayat evaluasi tetap utuh.',
            ]);
        }

        $aturanPrestasi->delete();

        return redirect()->route('admin.aturan-prestasi.index')->with('sukses', 'Aturan prestasi mendatang berhasil dihapus.');
    }
}
