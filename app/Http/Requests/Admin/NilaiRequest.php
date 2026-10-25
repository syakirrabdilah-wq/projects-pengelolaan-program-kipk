<?php

namespace App\Http\Requests\Admin;

use App\Models\Mahasiswa;
use App\Models\SemesterAkademik;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->aktif;
    }

    public function rules(): array
    {
        $penerima = $this->route('penerima');
        $semester = $this->route('semester');
        $mahasiswaId = $penerima instanceof Mahasiswa
            ? $penerima->id
            : ($semester instanceof SemesterAkademik ? $semester->mahasiswa_id : null);

        return [
            'semester_ke' => [
                'required', 'integer', 'between:1,255',
                Rule::unique('semester_akademik', 'semester_ke')
                    ->where(fn ($query) => $query->where('mahasiswa_id', $mahasiswaId))
                    ->ignore($semester instanceof SemesterAkademik ? $semester->id : null),
            ],
            'ips' => ['required', 'numeric', 'between:0,4'],
            'ipk' => ['required', 'numeric', 'between:0,4'],
            'sks' => ['required', 'integer', 'between:0,65535'],
            'file_khs' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }
}
