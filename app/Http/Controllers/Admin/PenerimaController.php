<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportPenerimaRequest;
use App\Http\Requests\Admin\PenerimaRequest;
use App\Imports\PenerimaImport;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PenerimaController extends Controller
{
    public function index()
    {
        $penerima = Mahasiswa::query()
            ->with('user')
            ->when(request('cari'), function ($query, $cari) {
                $query->where(function ($query) use ($cari) {
                    $query->where('nim', 'like', "%{$cari}%")
                        ->orWhere('nama', 'like', "%{$cari}%");
                });
            })
            ->when(request('prodi'), fn ($query, $prodi) => $query->where('prodi', $prodi))
            ->when(request('angkatan'), fn ($query, $angkatan) => $query->where('angkatan', $angkatan))
            ->when(request('status_kipk'), fn ($query, $status) => $query->where('status_kipk', $status))
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.penerima.index', [
            'penerima' => $penerima,
            'prodiList' => Mahasiswa::query()->select('prodi')->distinct()->orderBy('prodi')->pluck('prodi'),
            'angkatanList' => Mahasiswa::query()->select('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
            'statusList' => Mahasiswa::STATUS_KIPK,
        ]);
    }

    public function create()
    {
        return view('admin.penerima.create', ['statusList' => Mahasiswa::STATUS_KIPK]);
    }

    public function store(PenerimaRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['nim'],
                'password' => $data['password'],
                'role' => 'mahasiswa',
                'aktif' => true,
            ]);

            $user->mahasiswa()->create($this->dataMahasiswa($data));
        });

        return redirect()->route('admin.penerima.index')->with('sukses', 'Data penerima berhasil ditambahkan.');
    }

    public function show(Mahasiswa $penerima)
    {
        return view('admin.penerima.show', compact('penerima'));
    }

    public function edit(Mahasiswa $penerima)
    {
        return view('admin.penerima.edit', [
            'penerima' => $penerima,
            'statusList' => Mahasiswa::STATUS_KIPK,
        ]);
    }

    public function update(PenerimaRequest $request, Mahasiswa $penerima)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $penerima) {
            $penerima->update($this->dataMahasiswa($data));
            $penerima->user()->update(['username' => $data['nim']]);

            if (filled($data['password'] ?? null)) {
                $penerima->user()->update(['password' => $data['password']]);
            }
        });

        return redirect()->route('admin.penerima.show', $penerima)->with('sukses', 'Data penerima berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $penerima)
    {
        $penerima->user()->delete();

        return redirect()->route('admin.penerima.index')->with('sukses', 'Data penerima berhasil dihapus.');
    }

    public function importForm()
    {
        return view('admin.penerima.import');
    }

    public function import(ImportPenerimaRequest $request)
    {
        $import = new PenerimaImport;
        Excel::import($import, $request->file('file'));

        return redirect()->route('admin.penerima.import-form')->with('hasilImpor', [
            'berhasil' => $import->berhasil,
            'galat' => $import->galat,
        ]);
    }

    private function dataMahasiswa(array $data): array
    {
        return [
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'prodi' => $data['prodi'],
            'angkatan' => $data['angkatan'],
            'status_kipk' => $data['status_kipk'],
        ];
    }
}
