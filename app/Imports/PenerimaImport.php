<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenerimaImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    use RemembersChunkOffset;

    public int $berhasil = 0;

    /** @var array<int, array{baris: int, pesan: array<int, string>}> */
    public array $galat = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $data = $row->toArray();
            if (collect($data)->filter(fn ($value) => filled($value))->isEmpty()) {
                continue;
            }

            if (isset($data['nim']) && is_scalar($data['nim'])) {
                $data['nim'] = trim((string) $data['nim']);
            }

            $validator = Validator::make($data, [
                'nim' => [
                    'required', 'string', 'max:20',
                    Rule::unique('mahasiswa', 'nim'),
                    Rule::unique('users', 'username'),
                ],
                'nama' => ['required', 'string', 'max:100'],
                'prodi' => ['required', 'string', 'max:100'],
                'angkatan' => ['required', 'integer', 'between:1901,2155'],
                'status_kipk' => ['nullable', Rule::in(Mahasiswa::STATUS_KIPK)],
                'password' => ['required', 'string', 'min:8'],
                'konfirmasi_pencabutan' => ($data['status_kipk'] ?? null) === 'dicabut'
                    ? ['required', 'accepted']
                    : ['sometimes', 'accepted'],
            ]);

            if ($validator->fails()) {
                $this->galat[] = [
                    'baris' => ($this->getChunkOffset() ?? 1) + $index,
                    'pesan' => array_values($validator->errors()->all()),
                ];

                continue;
            }

            $valid = $validator->validated();

            DB::transaction(function () use ($valid) {
                $user = User::create([
                    'username' => $valid['nim'],
                    'password' => $valid['password'],
                    'role' => 'mahasiswa',
                    'aktif' => true,
                ]);

                $user->mahasiswa()->create([
                    'nim' => $valid['nim'],
                    'nama' => $valid['nama'],
                    'prodi' => $valid['prodi'],
                    'angkatan' => $valid['angkatan'],
                    'status_kipk' => $valid['status_kipk'] ?: 'aktif',
                ]);
            });

            $this->berhasil++;
        }
    }

    public function chunkSize(): int
    {
        return (int) config('imports.penerima_chunk_size', 500);
    }
}
