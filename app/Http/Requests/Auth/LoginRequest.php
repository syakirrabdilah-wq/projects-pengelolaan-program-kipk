<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureNotRateLimited();

        $credentials = [
            'username' => $this->string('username')->trim()->toString(),
            'password' => $this->input('password'),
            'aktif' => true,
        ];

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($this->throttleKey(), (int) config('auth.login.decay_seconds', 60));

            throw ValidationException::withMessages([
                'username' => 'Nama pengguna, kata sandi, atau status akun tidak sesuai.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function ensureNotRateLimited(): void
    {
        $maksimum = (int) config('auth.login.max_attempts', 5);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maksimum)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'username' => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.RateLimiter::availableIn($this->throttleKey()).' detik.',
        ]);
    }

    private function throttleKey(): string
    {
        return 'login:'.Str::transliterate(Str::lower($this->ip() ?? 'unknown'));
    }
}
