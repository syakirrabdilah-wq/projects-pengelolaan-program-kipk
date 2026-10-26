<?php

namespace App\Http\Requests\Admin;

use App\Models\Absensi;
use Illuminate\Foundation\Http\FormRequest;

class KoreksiAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->aktif;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:'.implode(',', Absensi::STATUS)],
        ];
    }
}
