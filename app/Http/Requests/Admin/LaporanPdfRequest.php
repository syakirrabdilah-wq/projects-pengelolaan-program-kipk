<?php

namespace App\Http\Requests\Admin;

class LaporanPdfRequest extends LaporanRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'semester' => ['required', 'integer', 'min:1', 'exists:semester_akademik,semester_ke'],
        ];
    }
}
