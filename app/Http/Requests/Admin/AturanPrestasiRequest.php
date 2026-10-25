<?php

namespace App\Http\Requests\Admin;

use App\Models\AturanPrestasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AturanPrestasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->aktif;
    }

    public function rules(): array
    {
        $aturan = $this->route('aturan_prestasi');

        return [
            'ipk_minimal' => ['required', 'numeric', 'between:0,4'],
            'berlaku_mulai' => [
                'required', 'date',
                Rule::unique('aturan_prestasi', 'berlaku_mulai')->ignore($aturan instanceof AturanPrestasi ? $aturan->id : null),
            ],
        ];
    }
}
