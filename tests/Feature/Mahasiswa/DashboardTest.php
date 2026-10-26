<?php

namespace Tests\Feature\Mahasiswa;

use App\Models\AturanPrestasi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_mahasiswa_hanya_menampilkan_data_milik_pengguna_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $milikSaya = $this->mahasiswa($admin, 'Mahasiswa Saya', 3.20);
        $orangLain = $this->mahasiswa($admin, 'Rahasia Mahasiswa Lain', 1.10);
        AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => today()]);
        $kegiatan = KegiatanPembinaan::create([
            'judul' => 'Pembinaan selesai',
            'tanggal' => today(),
            'absen_buka' => now()->subHours(3),
            'absen_tutup' => now()->subHour(),
            'dibuat_oleh' => $admin->id,
        ]);
        $milikSaya->absensi()->create([
            'kegiatan_id' => $kegiatan->id,
            'waktu' => now()->subHours(2),
            'status' => 'hadir',
        ]);

        $this->actingAs($milikSaya->user)
            ->get(route('mahasiswa.dashboard', ['mahasiswa_id' => $orangLain->id]))
            ->assertOk()
            ->assertSee('Halo, Mahasiswa Saya')
            ->assertSee('3.20')
            ->assertSee('100.0%')
            ->assertDontSee('dokumentasi minggu ini')
            ->assertDontSee('Rahasia Mahasiswa Lain')
            ->assertDontSee('1.10');
    }

    public function test_dashboard_mahasiswa_menangani_keadaan_kosong_dan_admin_ditolak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->mahasiswa($admin, 'Mahasiswa Baru', null);
        $this->actingAs($mahasiswa->user)->get(route('mahasiswa.dashboard'))
            ->assertOk()
            ->assertSee('Belum tersedia')
            ->assertSee('Belum ada kegiatan selesai')
            ->assertDontSee('Dokumentasi minggu ini');

        $this->actingAs($admin)->get(route('mahasiswa.dashboard'))->assertForbidden();
    }

    private function mahasiswa(User $admin, string $nama, ?float $ipk): Mahasiswa
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $mahasiswa = $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => $nama,
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2026,
            'status_kipk' => 'aktif',
        ]);
        if ($ipk !== null) {
            $mahasiswa->semester()->create([
                'semester_ke' => 1,
                'ips' => $ipk,
                'ipk' => $ipk,
                'sks' => 20,
                'diinput_oleh' => $admin->id,
                'created_at' => now(),
            ]);
        }

        return $mahasiswa;
    }
}
