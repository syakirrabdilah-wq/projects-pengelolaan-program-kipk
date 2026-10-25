<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifikasi = $request->user()->notifikasi()
            ->latest('created_at')
            ->paginate(20);

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function baca(Request $request, Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 404);

        if (! $notifikasi->sudah_dibaca) {
            $notifikasi->update(['sudah_dibaca' => true]);
        }

        return redirect()->route('notifikasi.index')->with('sukses', 'Notifikasi ditandai sudah dibaca.');
    }
}
