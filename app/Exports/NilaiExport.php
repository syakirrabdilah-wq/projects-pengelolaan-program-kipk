<?php

namespace App\Exports;

use App\Exports\Concerns\AppliesMahasiswaFilters;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NilaiExport implements FromQuery, WithCustomChunkSize, WithHeadings, WithMapping
{
    use AppliesMahasiswaFilters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = DB::table('semester_akademik as s')
            ->join('mahasiswa as m', 'm.id', '=', 's.mahasiswa_id')
            ->select(['m.nim', 'm.nama', 'm.prodi', 'm.angkatan', 's.semester_ke', 's.ips', 's.ipk', 's.sks'])
            ->orderBy('m.nama')
            ->orderBy('s.semester_ke');

        $this->filterMahasiswa($query);

        return $query->when($this->filters['semester'] ?? null, fn (Builder $query, int|string $semester) => $query->where('s.semester_ke', $semester));
    }

    public function headings(): array
    {
        return ['NIM', 'Nama', 'Program Studi', 'Angkatan', 'Semester', 'IPS', 'IPK', 'SKS'];
    }

    public function map($row): array
    {
        return [
            $this->amanUntukSpreadsheet($row->nim),
            $this->amanUntukSpreadsheet($row->nama),
            $this->amanUntukSpreadsheet($row->prodi),
            $row->angkatan,
            $row->semester_ke,
            $row->ips,
            $row->ipk,
            $row->sks,
        ];
    }

    public function chunkSize(): int
    {
        return (int) config('laporan.ukuran_chunk', 500);
    }
}
