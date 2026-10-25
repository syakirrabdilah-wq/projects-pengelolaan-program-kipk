<?php

namespace App\Http\Requests\Admin;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class KegiatanPembinaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->aktif;
    }

    protected function prepareForValidation(): void
    {
        foreach (['absen_buka', 'absen_tutup'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => str_replace('T', ' ', $this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'absen_buka' => ['required', 'date_format:Y-m-d H:i'],
            'absen_tutup' => ['required', 'date_format:Y-m-d H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['absen_buka', 'absen_tutup'])) {
                return;
            }

            $buka = CarbonImmutable::createFromFormat('!Y-m-d H:i', $this->input('absen_buka'), config('app.timezone'));
            $tutup = CarbonImmutable::createFromFormat('!Y-m-d H:i', $this->input('absen_tutup'), config('app.timezone'));

            if ($tutup->lessThanOrEqualTo($buka)) {
                $validator->errors()->add('absen_tutup', 'Waktu tutup harus setelah waktu buka.');
            }
        });
    }
}
