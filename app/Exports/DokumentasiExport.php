<?php

namespace App\Exports;

use App\Exports\Concerns\AppliesMahasiswaFilters;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DokumentasiExport implements FromQuery, WithCustomChunkSize, WithHeadings, WithMapping
{
    use AppliesMahasiswaFilters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = DB::table('dokumentasi_mingguan as d')
            ->join('mahasiswa as m', 'm.id', '=', 'd.mahasiswa_id')
            ->select([
                'm.nim', 'm.nama', 'm.prodi', 'm.angkatan', 'd.tahun', 'd.minggu_ke',
                'd.status_verifikasi', 'd.keterangan', 'd.catatan_admin', 'd.created_at',
            ])
            ->orderBy('d.tahun')
            ->orderBy('d.minggu_ke')
            ->orderBy('m.nama');

        return $this->filterMahasiswa($query);
    }

    public function headings(): array
    {
        return ['NIM', 'Nama', 'Program Studi', 'Angkatan', 'Tahun ISO', 'Minggu ISO', 'Status', 'Keterangan', 'Catatan Admin', 'Tanggal Kirim'];
    }

    public function map($row): array
    {
        return [
            $this->amanUntukSpreadsheet($row->nim),
            $this->amanUntukSpreadsheet($row->nama),
            $this->amanUntukSpreadsheet($row->prodi),
            $row->angkatan,
            $row->tahun,
            $row->minggu_ke,
            $row->status_verifikasi,
            $this->amanUntukSpreadsheet($row->keterangan),
            $this->amanUntukSpreadsheet($row->catatan_admin),
            $row->created_at,
        ];
    }

    public function chunkSize(): int
    {
        return (int) config('laporan.ukuran_chunk', 500);
    }
}
