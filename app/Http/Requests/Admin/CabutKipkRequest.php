<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CabutKipkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->aktif;
    }

    public function rules(): array
    {
        return ['konfirmasi' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return ['konfirmasi.accepted' => 'Konfirmasi pencabutan wajib diberikan.'];
    }
}
