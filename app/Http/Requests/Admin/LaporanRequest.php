<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'prodi' => ['nullable', 'string', 'max:100'],
            'angkatan' => ['nullable', 'integer', 'between:1900,2100'],
            'semester' => ['nullable', 'integer', 'min:1', 'exists:semester_akademik,semester_ke'],
        ];
    }
}
