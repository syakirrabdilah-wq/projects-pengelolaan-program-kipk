<?php

namespace App\Exports\Concerns;

use Illuminate\Database\Query\Builder;

trait AppliesMahasiswaFilters
{
    protected array $filters;

    protected function filterMahasiswa(Builder $query, string $alias = 'm'): Builder
    {
        return $query
            ->when($this->filters['prodi'] ?? null, fn (Builder $query, string $prodi) => $query->where("{$alias}.prodi", $prodi))
            ->when($this->filters['angkatan'] ?? null, fn (Builder $query, int|string $angkatan) => $query->where("{$alias}.angkatan", $angkatan));
    }

    protected function amanUntukSpreadsheet(?string $nilai): string
    {
        if ($nilai !== null && preg_match('/^[=+\-@\t\r]/u', $nilai) === 1) {
            return "'{$nilai}";
        }

        return $nilai ?? '';
    }
}
