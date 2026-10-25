<?php

namespace Tests\Feature\Mahasiswa;

use App\Models\AturanPrestasi;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_nilai_selalu_memakai_mahasiswa_dari_sesi_dan_hanya_menampilkan_nilai_sendiri(): void
    {
        $milikSaya = $this->mahasiswaDenganNilai(2.90);
        $milikOrangLain = $this->mahasiswaDenganNilai(1.20);
        AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => now()->toDateString()]);

        $this->actingAs($milikSaya->user)
            ->get(route('mahasiswa.nilai.index', ['mahasiswa_id' => $milikOrangLain->id]))
            ->assertOk()
            ->assertSee('2.90')
            ->assertDontSee('1.20')
            ->assertSee('di bawah batas');
    }

    public function test_mahasiswa_tidak_memiliki_rute_tulis_nilai_dan_tidak_dapat_membuka_halaman_admin(): void
    {
        $mahasiswa = $this->mahasiswaDenganNilai(2.90);

        $this->actingAs($mahasiswa->user)->post('/mahasiswa/nilai', [])->assertMethodNotAllowed();
        $this->actingAs($mahasiswa->user)->put('/mahasiswa/nilai', [])->assertMethodNotAllowed();
        $this->actingAs($mahasiswa->user)->get(route('admin.peringatan.index'))->assertForbidden();
        $this->actingAs($mahasiswa->user)->get(route('admin.pengaturan.edit'))->assertForbidden();
    }

    private function mahasiswaDenganNilai(float $ipk): Mahasiswa
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $mahasiswa = $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2026,
            'status_kipk' => 'aktif',
        ]);
        $mahasiswa->semester()->create([
            'semester_ke' => 1,
            'ips' => $ipk,
            'ipk' => $ipk,
            'sks' => 20,
            'diinput_oleh' => $admin->id,
            'created_at' => now(),
        ]);

        return $mahasiswa;
    }
}
