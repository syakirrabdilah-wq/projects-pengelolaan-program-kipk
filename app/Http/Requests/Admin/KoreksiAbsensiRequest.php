<?php

namespace App\Http\Requests\Admin;

use App\Models\Absensi;
use Illuminate\Contracts\Validation\Validator;
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
            'file_foto' => ['required', 'image', 'mimetypes:image/jpeg,image/png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file_foto.required' => 'Unggah foto kegiatan sebagai bukti koreksi absensi.',
            'file_foto.image' => 'Berkas yang dipilih harus berupa gambar.',
            'file_foto.mimetypes' => 'Foto harus berformat JPG atau PNG.',
            'file_foto.max' => 'Ukuran foto maksimal 5 MB.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $foto = $this->file('file_foto');

            if (! $foto || $validator->errors()->has('file_foto')) {
                return;
            }

            $dimensi = @getimagesize($foto->getRealPath());
            $piksel = is_array($dimensi) ? ((int) $dimensi[0] * (int) $dimensi[1]) : 0;

            if ($piksel < 1 || $piksel > (int) config('dokumentasi.piksel_maksimum')) {
                $validator->errors()->add('file_foto', 'Resolusi foto terlalu besar atau tidak valid.');
            }
        }];
    }
}
