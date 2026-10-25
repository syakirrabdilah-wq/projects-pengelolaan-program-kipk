<?php

namespace Tests\Feature\Command;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BuatAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_membuat_admin_dengan_kata_sandi_tersembunyi_dan_hash(): void
    {
        $this->artisan('kipk:admin-create', ['username' => 'admin-utama'])
            ->expectsQuestion('Kata sandi admin (minimal 8 karakter)', 'kata-sandi-kuat')
            ->expectsQuestion('Ulangi kata sandi', 'kata-sandi-kuat')
            ->assertSuccessful();

        $admin = User::where('username', 'admin-utama')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->aktif);
        $this->assertTrue(Hash::check('kata-sandi-kuat', $admin->password));
    }

    public function test_command_menolak_username_yang_sudah_dipakai(): void
    {
        User::factory()->create(['username' => 'admin-utama', 'role' => 'mahasiswa']);

        $this->artisan('kipk:admin-create', ['username' => 'admin-utama'])
            ->expectsOutputToContain('has already been taken')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }
}
