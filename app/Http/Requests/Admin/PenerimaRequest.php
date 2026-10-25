<?php

namespace App\Http\Requests\Admin;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenerimaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->aktif;
    }

    public function rules(): array
    {
        $penerima = $this->route('penerima');
        $userId = $penerima instanceof Mahasiswa ? $penerima->user_id : null;

        return [
            'nim' => [
                'required', 'string', 'max:20',
                Rule::unique('mahasiswa', 'nim')->ignore($penerima),
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'prodi' => ['required', 'string', 'max:100'],
            'angkatan' => ['required', 'integer', 'between:1901,2155'],
            'status_kipk' => ['required', Rule::in(Mahasiswa::STATUS_KIPK)],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8'],
            'konfirmasi_pencabutan' => $this->input('status_kipk') === 'dicabut'
                ? ['required', 'accepted']
                : ['sometimes', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return ['konfirmasi_pencabutan.accepted' => 'Konfirmasi pencabutan wajib dicentang.'];
    }
}
