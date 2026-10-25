<?php

namespace Tests\Feature\Admin;

use App\Models\AturanPrestasi;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeringatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_peringatan_menandai_tingkat_yang_mencapai_ambang(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->mahasiswaDenganPeringatan($admin, 3);
        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '3']);

        $this->actingAs($admin)->get(route('admin.peringatan.index'))
            ->assertOk()->assertSee('Pencabutan')->assertSee($mahasiswa->nama);
    }

    public function test_admin_harus_mengonfirmasi_dan_mahasiswa_tidak_dapat_mencabut(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->mahasiswaDenganPeringatan($admin, 3);
        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '3']);

        $this->actingAs($admin)->post(route('admin.peringatan.cabut', $mahasiswa), [])
            ->assertSessionHasErrors('konfirmasi');
        $this->assertSame('aktif', $mahasiswa->fresh()->status_kipk);
        $this->actingAs($mahasiswa->user)->post(route('admin.peringatan.cabut', $mahasiswa), ['konfirmasi' => '1'])
            ->assertForbidden();
        $this->assertSame('aktif', $mahasiswa->fresh()->status_kipk);
    }

    public function test_hanya_admin_dapat_mencabut_mahasiswa_yang_mencapai_ambang_dengan_konfirmasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $layak = $this->mahasiswaDenganPeringatan($admin, 3);
        $belumLayak = $this->mahasiswaDenganPeringatan($admin, 2);
        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '3']);

        $this->actingAs($admin)->post(route('admin.peringatan.cabut', $belumLayak), ['konfirmasi' => '1'])
            ->assertSessionHasErrors('pencabutan');
        $this->assertSame('aktif', $belumLayak->fresh()->status_kipk);
        $this->actingAs($admin)->post(route('admin.peringatan.cabut', $layak), ['konfirmasi' => '1'])
            ->assertRedirect(route('admin.peringatan.index'));
        $this->assertSame('dicabut', $layak->fresh()->status_kipk);
    }

    private function mahasiswaDenganPeringatan(User $admin, int $tingkat): Mahasiswa
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $mahasiswa = $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2026,
            'status_kipk' => 'aktif',
        ]);
        $semester = $mahasiswa->semester()->create([
            'semester_ke' => $tingkat,
            'ips' => 2.50,
            'ipk' => 2.50,
            'sks' => 20,
            'diinput_oleh' => $admin->id,
            'created_at' => now(),
        ]);
        $aturan = AturanPrestasi::firstOrCreate(
            ['berlaku_mulai' => now()->toDateString()],
            ['ipk_minimal' => 3.00],
        );
        $mahasiswa->peringatan()->create([
            'semester_id' => $semester->id,
            'aturan_id' => $aturan->id,
            'tingkat' => $tingkat,
            'tanggal' => now()->toDateString(),
        ]);

        return $mahasiswa;
    }
}
