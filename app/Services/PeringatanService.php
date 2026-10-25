<?php

namespace App\Services;

use App\Models\AturanPrestasi;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\Peringatan;
use Illuminate\Support\Collection;

class PeringatanService
{
    /**
     * Evaluate every stored semester and synchronize its warning rows.
     * Notifications are intentionally handled by the later T-205 work.
     *
     * @return Collection<int, Peringatan>
     */
    public function evaluasi(Mahasiswa $mahasiswa): Collection
    {
        $batasPencabutan = $this->batasPencabutan();
        $semesterList = $mahasiswa->semester()->orderBy('semester_ke')->get();
        $expected = [];
        $rangkaian = 0;
        $semesterSebelumnya = null;

        foreach ($semesterList as $semester) {
            $berurutan = $semesterSebelumnya !== null
                && $semester->semester_ke === $semesterSebelumnya + 1;

            if (! $berurutan) {
                $rangkaian = 0;
            }

            $aturan = AturanPrestasi::query()
                ->whereDate('berlaku_mulai', '<=', $semester->created_at->toDateString())
                ->orderByDesc('berlaku_mulai')
                ->first();

            if ($aturan && (float) $semester->ipk < (float) $aturan->ipk_minimal) {
                $rangkaian = $berurutan ? $rangkaian + 1 : 1;
                $expected[$semester->id] = [
                    'mahasiswa_id' => $mahasiswa->id,
                    'aturan_id' => $aturan->id,
                    'semester_id' => $semester->id,
                    'tingkat' => $rangkaian,
                    'tanggal' => $semester->created_at->toDateString(),
                ];
            } else {
                $rangkaian = 0;
            }

            $semesterSebelumnya = $semester->semester_ke;
        }

        $query = Peringatan::query()->where('mahasiswa_id', $mahasiswa->id);

        if ($expected === []) {
            $query->delete();
        } else {
            $query->whereNotIn('semester_id', array_keys($expected))->delete();
        }

        $warnings = collect();
        foreach ($expected as $attributes) {
            $warning = Peringatan::updateOrCreate(
                ['mahasiswa_id' => $mahasiswa->id, 'semester_id' => $attributes['semester_id']],
                $attributes,
            );
            $warning->setAttribute('memenuhi_batas_pencabutan', $warning->tingkat >= $batasPencabutan);
            $warnings->push($warning);
        }

        return $warnings;
    }

    public function batasPencabutan(): int
    {
        return (int) (Pengaturan::query()
            ->where('kunci', 'peringatan_batas_pencabutan')
            ->value('nilai') ?? 3);
    }
}
