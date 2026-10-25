<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AkunController extends Controller
{
    public function index(Request $request)
    {
        $akun = User::query()
            ->with('mahasiswa:id,user_id,nim,nama')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $cari = $request->string('cari')->toString();
                $query->where(function ($query) use ($cari) {
                    $query->where('username', 'like', "%{$cari}%")
                        ->orWhereHas('mahasiswa', fn ($mahasiswa) => $mahasiswa
                            ->where('nim', 'like', "%{$cari}%")
                            ->orWhere('nama', 'like', "%{$cari}%"));
                });
            })
            ->when(in_array($request->query('role'), ['admin', 'mahasiswa'], true),
                fn ($query) => $query->where('role', $request->query('role')))
            ->orderBy('role')
            ->orderBy('username')
            ->paginate(20)
            ->withQueryString();

        return view('admin.akun.index', compact('akun'));
    }

    public function resetPassword(Request $request, User $akun)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $akun->update(['password' => $data['password']]);

        return back()->with('sukses', "Password akun {$akun->username} berhasil direset.");
    }

    public function toggleAktif(Request $request, User $akun)
    {
        if ($akun->is($request->user())) {
            throw ValidationException::withMessages([
                'akun' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan.',
            ]);
        }

        if ($akun->isAdmin() && $akun->aktif) {
            $berhasil = DB::transaction(function () use ($akun) {
                $adminAktifLain = User::query()
                    ->where('role', 'admin')
                    ->where('aktif', true)
                    ->whereKeyNot($akun->id)
                    ->lockForUpdate()
                    ->exists();

                if (! $adminAktifLain) {
                    return false;
                }

                $akun->update(['aktif' => false]);

                return true;
            });

            if (! $berhasil) {
                throw ValidationException::withMessages([
                    'akun' => 'Akun admin terakhir yang aktif tidak dapat dinonaktifkan.',
                ]);
            }
        } else {
            $akun->update(['aktif' => ! $akun->aktif]);
        }

        $status = $akun->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('sukses', "Akun {$akun->username} berhasil {$status}.");
    }
}
