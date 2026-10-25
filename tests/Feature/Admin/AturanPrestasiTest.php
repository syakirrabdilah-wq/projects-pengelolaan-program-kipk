<?php

namespace Tests\Feature\Admin;

use App\Models\AturanPrestasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AturanPrestasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuat_dan_mengubah_aturan_prestasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.aturan-prestasi.store'), [
            'ipk_minimal' => 3.00,
            'berlaku_mulai' => '2026-01-01',
        ])->assertRedirect(route('admin.aturan-prestasi.index'));

        $aturan = AturanPrestasi::firstOrFail();
        $this->assertSame('3.00', $aturan->ipk_minimal);

        $this->actingAs($admin)->put(route('admin.aturan-prestasi.update', $aturan), [
            'ipk_minimal' => 3.25,
            'berlaku_mulai' => '2026-01-01',
        ])->assertRedirect(route('admin.aturan-prestasi.index'));

        $this->assertDatabaseHas('aturan_prestasi', ['id' => $aturan->id, 'ipk_minimal' => '3.25']);
    }

    public function test_aturan_memvalidasi_batas_ipk_dan_tanggal_berlaku_unik(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => '2026-01-01']);

        $this->actingAs($admin)->post(route('admin.aturan-prestasi.store'), [
            'ipk_minimal' => 4.01,
            'berlaku_mulai' => '2026-01-01',
        ])->assertSessionHasErrors(['ipk_minimal', 'berlaku_mulai']);
    }

    public function test_mahasiswa_ditolak_dari_rute_aturan_prestasi(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($mahasiswa)
            ->get(route('admin.aturan-prestasi.index'))
            ->assertForbidden();
    }

    public function test_aturan_yang_sudah_berlaku_tidak_dapat_dihapus_dan_aturan_mendatang_dapat_dihapus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $berlaku = AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => '2026-01-01']);
        $mendatang = AturanPrestasi::create(['ipk_minimal' => 3.10, 'berlaku_mulai' => '2099-01-01']);

        $this->actingAs($admin)
            ->delete(route('admin.aturan-prestasi.destroy', $berlaku))
            ->assertSessionHasErrors('aturan');
        $this->assertDatabaseHas('aturan_prestasi', ['id' => $berlaku->id]);

        $this->actingAs($admin)
            ->delete(route('admin.aturan-prestasi.destroy', $mendatang))
            ->assertRedirect(route('admin.aturan-prestasi.index'));
        $this->assertDatabaseMissing('aturan_prestasi', ['id' => $mendatang->id]);
    }
}
