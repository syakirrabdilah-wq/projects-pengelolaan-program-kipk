<?php

namespace App\Exports;

use App\Exports\Concerns\AppliesMahasiswaFilters;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KehadiranExport implements FromQuery, WithCustomChunkSize, WithHeadings, WithMapping
{
    use AppliesMahasiswaFilters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = DB::table('kegiatan_pembinaan as k')
            ->crossJoin('mahasiswa as m')
            ->leftJoin('absensi as a', function ($join) {
                $join->on('a.kegiatan_id', '=', 'k.id')->on('a.mahasiswa_id', '=', 'm.id');
            })
            ->where('m.status_kipk', 'aktif')
            ->select([
                'm.nim', 'm.nama', 'm.prodi', 'm.angkatan', 'k.judul', 'k.tanggal',
                DB::raw("COALESCE(a.status, 'Tidak hadir') AS status_kehadiran"),
            ])
            ->orderBy('k.tanggal')
            ->orderBy('m.nama');

        return $this->filterMahasiswa($query);
    }

    public function headings(): array
    {
        return ['NIM', 'Nama', 'Program Studi', 'Angkatan', 'Kegiatan', 'Tanggal', 'Status Kehadiran'];
    }

    public function map($row): array
    {
        return [
            $this->amanUntukSpreadsheet($row->nim),
            $this->amanUntukSpreadsheet($row->nama),
            $this->amanUntukSpreadsheet($row->prodi),
            $row->angkatan,
            $this->amanUntukSpreadsheet($row->judul),
            $row->tanggal,
            $row->status_kehadiran,
        ];
    }

    public function chunkSize(): int
    {
        return (int) config('laporan.ukuran_chunk', 500);
    }
}
