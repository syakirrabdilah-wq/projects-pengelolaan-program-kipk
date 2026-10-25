<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiDokumentasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->aktif;
    }

    public function rules(): array
    {
        return [
            'status_verifikasi' => ['required', 'in:diterima,ditolak'],
            'catatan_admin' => ['required', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'status_verifikasi.required' => 'Pilih hasil verifikasi.',
            'status_verifikasi.in' => 'Hasil verifikasi harus Terima atau Tolak.',
            'catatan_admin.required' => 'Catatan verifikasi wajib diisi.',
            'catatan_admin.max' => 'Catatan maksimal 200 karakter.',
        ];
    }
}
