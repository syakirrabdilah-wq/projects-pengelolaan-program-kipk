<?php

namespace App\Observers;

use App\Models\AturanPrestasi;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\SemesterAkademik;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditPerubahanObserver
{
    public function created(Model $model): void
    {
        $field = $this->fieldYangDicatat($model);
        if ($field !== []) {
            app(AuditLogService::class)->catat($model, 'dibuat', $field, [], $model->getAttributes());
        }
    }

    public function updated(Model $model): void
    {
        $field = array_intersect($this->fieldYangDicatat($model), array_keys($model->getChanges()));
        if ($field !== []) {
            app(AuditLogService::class)->catat($model, 'diubah', $field, $model->getRawOriginal(), $model->getAttributes());
        }
    }

    public function deleted(Model $model): void
    {
        $field = $this->fieldYangDicatat($model);
        if ($field !== []) {
            app(AuditLogService::class)->catat($model, 'dihapus', $field, $model->getAttributes(), []);
        }
    }

    private function fieldYangDicatat(Model $model): array
    {
        return match (true) {
            $model instanceof SemesterAkademik => ['semester_ke', 'ips', 'ipk', 'sks'],
            $model instanceof Mahasiswa => ['status_kipk'],
            $model instanceof AturanPrestasi => ['ipk_minimal', 'berlaku_mulai'],
            $model instanceof Pengaturan => ['nilai'],
            default => [],
        };
    }
}
