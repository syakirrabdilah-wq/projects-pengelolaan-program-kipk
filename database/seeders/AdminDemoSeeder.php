<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $admins = collect(config('admin_demo.admins', []));

        if ($admins->pluck('username')->filter()->unique()->count() !== 3) {
            throw new InvalidArgumentException('Username tiga admin demo harus diisi dan berbeda.');
        }

        foreach ($admins as $admin) {
            if (blank($admin['password'])) {
                throw new InvalidArgumentException('Isi password admin demo di .env sebelum menjalankan seeder.');
            }

            $user = User::firstOrNew(['username' => $admin['username']]);

            if ($user->exists && ! $user->isAdmin()) {
                throw new InvalidArgumentException('Username admin demo sudah dipakai akun non-admin.');
            }

            $user->forceFill([
                'password' => $admin['password'],
                'role' => 'admin',
                'aktif' => true,
            ])->save();
        }
    }
}
