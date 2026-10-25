<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class DokumentasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'mahasiswa'
            && $this->user()->aktif
            && $this->user()->mahasiswa !== null;
    }

    public function rules(): array
    {
        return [
            'file_foto' => ['required', 'image', 'mimetypes:image/jpeg,image/png', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'file_foto.required' => 'Pilih foto dokumentasi terlebih dahulu.',
            'file_foto.image' => 'Berkas yang dipilih harus berupa gambar.',
            'file_foto.mimetypes' => 'Foto harus berformat JPG atau PNG.',
            'file_foto.max' => 'Ukuran foto maksimal 5 MB.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ];
    }
}
