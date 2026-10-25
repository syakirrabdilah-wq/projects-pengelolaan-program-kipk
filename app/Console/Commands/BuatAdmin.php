<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class BuatAdmin extends Command
{
    protected $signature = 'kipk:admin-create {username? : Nama pengguna admin baru}';

    protected $description = 'Buat akun admin awal dengan input kata sandi tersembunyi.';

    public function handle(): int
    {
        $username = $this->argument('username') ?: $this->ask('Nama pengguna admin');
        $validator = Validator::make(['username' => $username], [
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
        ]);

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first('username'));

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi admin (minimal 8 karakter)');
        if (! is_string($password) || strlen($password) < 8) {
            $this->components->error('Kata sandi harus berisi minimal 8 karakter.');

            return self::FAILURE;
        }

        if (! hash_equals($password, (string) $this->secret('Ulangi kata sandi'))) {
            $this->components->error('Konfirmasi kata sandi tidak cocok.');

            return self::FAILURE;
        }

        User::create([
            'username' => $username,
            'password' => $password,
            'role' => 'admin',
            'aktif' => true,
        ]);

        $this->components->info("Akun admin {$username} berhasil dibuat.");

        return self::SUCCESS;
    }
}
