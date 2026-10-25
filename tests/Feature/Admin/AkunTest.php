<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_mencari_dan_memfilter_akun(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin1']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa', 'username' => 'mhs1']);
        $mahasiswa->mahasiswa()->create([
            'nim' => '20260001',
            'nama' => 'Dewi Putri',
            'prodi' => 'Informatika',
            'angkatan' => 2026,
            'status_kipk' => 'aktif',
        ]);

        $this->actingAs($admin)->get(route('admin.akun.index', ['role' => 'mahasiswa', 'cari' => 'Dewi']))
            ->assertOk()
            ->assertSee('Dewi Putri')
            ->assertDontSee('admin1');
    }

    public function test_admin_dapat_reset_password_dan_menonaktifkan_akun_mahasiswa(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($admin)->put(route('admin.akun.password', $mahasiswa), [
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('password-baru-123', $mahasiswa->fresh()->password));

        $this->put(route('admin.akun.status', $mahasiswa))->assertRedirect();
        $this->assertFalse($mahasiswa->fresh()->aktif);
        $this->put(route('admin.akun.status', $mahasiswa))->assertRedirect();
        $this->assertTrue($mahasiswa->fresh()->aktif);
    }

    public function test_admin_tidak_dapat_menonaktifkan_dirinya_dan_admin_lain_tidak_mengunci_akun_aktif(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.akun.status', $admin))
            ->assertSessionHasErrors('akun');
        $this->assertTrue($admin->fresh()->aktif);

        $adminLain = User::factory()->create(['role' => 'admin']);
        $this->put(route('admin.akun.status', $adminLain))->assertRedirect();
        $this->assertFalse($adminLain->fresh()->aktif);
    }

    public function test_admin_dapat_reset_password_admin_lain_tanpa_mengunci_admin_aktif_terakhir(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminLain = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.akun.password', $adminLain), [
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('password-baru-123', $adminLain->fresh()->password));

        $this->put(route('admin.akun.status', $admin))->assertRedirect();
        $this->put(route('admin.akun.status', $adminLain))->assertRedirect();
        $this->assertFalse($adminLain->fresh()->aktif);
        $this->assertTrue($admin->fresh()->aktif);
    }

    public function test_mahasiswa_tidak_dapat_membuka_rute_admin(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($mahasiswa)->get(route('admin.akun.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }
}
