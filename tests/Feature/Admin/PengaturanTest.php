<?php

namespace Tests\Feature\Admin;

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_dan_memperbarui_pengaturan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.pengaturan.edit'))
            ->assertOk()->assertSee('Hari tenggat dokumentasi');
        $this->actingAs($admin)->put(route('admin.pengaturan.update'), [
            'dokumentasi_tenggat_hari' => 5,
            'dokumentasi_tenggat_jam' => '17:30',
            'peringatan_batas_pencabutan' => 4,
        ])->assertRedirect(route('admin.pengaturan.edit'));

        $this->assertDatabaseHas('pengaturan', ['kunci' => 'dokumentasi_tenggat_hari', 'nilai' => '5', 'diubah_oleh' => $admin->id]);
        $this->assertDatabaseHas('pengaturan', ['kunci' => 'dokumentasi_tenggat_jam', 'nilai' => '17:30']);
        $this->assertDatabaseHas('pengaturan', ['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '4']);
    }

    public function test_pengaturan_memvalidasi_hari_jam_dan_batas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.pengaturan.update'), [
            'dokumentasi_tenggat_hari' => 8,
            'dokumentasi_tenggat_jam' => '25:70',
            'peringatan_batas_pencabutan' => 0,
        ])->assertSessionHasErrors([
            'dokumentasi_tenggat_hari', 'dokumentasi_tenggat_jam', 'peringatan_batas_pencabutan',
        ]);
    }

    public function test_mahasiswa_tidak_dapat_membuka_atau_mengubah_pengaturan(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($mahasiswa)->get(route('admin.pengaturan.edit'))->assertForbidden();
        $this->actingAs($mahasiswa)->put(route('admin.pengaturan.update'), [])->assertForbidden();
        $this->assertSame(0, Pengaturan::count());
    }
}
