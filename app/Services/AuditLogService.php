<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function catat(Model $model, string $aksi, array $field, array $sebelum, array $sesudah): void
    {
        $pengguna = auth()->user();
        if (! $pengguna?->isAdmin()) {
            return;
        }

        $perubahan = [];
        foreach ($field as $nama) {
            $nilaiLama = $sebelum[$nama] ?? null;
            $nilaiBaru = $sesudah[$nama] ?? null;

            if ($nilaiLama !== $nilaiBaru) {
                $perubahan[$nama] = ['dari' => $nilaiLama, 'menjadi' => $nilaiBaru];
            }
        }

        if ($perubahan === []) {
            return;
        }

        AuditLog::create([
            'user_id' => $pengguna->id,
            'jenis_data' => $model->getTable(),
            'data_id' => (string) $model->getKey(),
            'aksi' => $aksi,
            'perubahan' => $perubahan,
            'created_at' => now(),
        ]);
    }
}
