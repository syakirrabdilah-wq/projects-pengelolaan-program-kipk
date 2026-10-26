<?php

namespace Tests\Feature\Admin;

use App\Models\AturanPrestasi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_admin_tampilkan_ringkasan_grafik_dan_mahasiswa_bermasalah(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rendah = $this->mahasiswa($admin, ['ipk' => 2.80, 'prodi' => 'Informatika', 'angkatan' => 2024]);
        $sehat = $this->mahasiswa($admin, ['ipk' => 3.50, 'prodi' => 'Informatika', 'angkatan' => 2024]);
        $nonaktif = $this->mahasiswa($admin, ['ipk' => 2.00, 'status_kipk' => 'lulus', 'prodi' => 'Manajemen', 'angkatan' => 2023]);
        AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => today()]);
        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '3']);
        $semester = $rendah->semester()->firstOrFail();
        $rendah->peringatan()->create([
            'semester_id' => $semester->id,
            'aturan_id' => AturanPrestasi::first()->id,
            'tingkat' => 3,
            'tanggal' => today(),
        ]);

        foreach (range(1, 4) as $nomor) {
            $kegiatan = KegiatanPembinaan::create([
                'judul' => "Kegiatan {$nomor}",
                'tanggal' => today(),
                'absen_buka' => now()->subHours(3),
                'absen_tutup' => now()->subHour(),
                'dibuat_oleh' => $admin->id,
            ]);
            if ($nomor <= 2) {
                $rendah->absensi()->create([
                    'kegiatan_id' => $kegiatan->id,
                    'waktu' => now()->subHours(2),
                    'status' => 'hadir',
                ]);
            }
            $sehat->absensi()->create([
                'kegiatan_id' => $kegiatan->id,
                'waktu' => now()->subHours(2),
                'status' => 'hadir',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Total penerima')
            ->assertSee('Memenuhi syarat pencabutan')
            ->assertSee('grafik-prodi')
            ->assertSee('grafik-angkatan')
            ->assertSee($rendah->nama)
            ->assertSee('IPK di bawah batas')
            ->assertSee('Kehadiran rendah')
            ->assertDontSee('Belum kirim dokumentasi minggu ini')
            ->assertDontSee($nonaktif->nama);

        $response->assertViewHas('ringkasan', fn (array $summary) => $summary['total_penerima'] === 3
            && $summary['penerima_aktif'] === 2
            && $summary['dalam_peringatan'] === 1
            && $summary['memenuhi_syarat_pencabutan'] === 1
        );
    }

    public function test_dashboard_admin_menangani_keadaan_kosong_dan_mahasiswa_ditolak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada data penerima')
            ->assertSee('Belum ada mahasiswa yang perlu ditinjau');

        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($mahasiswa)->get(route('admin.dashboard'))->assertForbidden();
    }

    private function mahasiswa(User $admin, array $atribut = []): Mahasiswa
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $mahasiswa = $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'prodi' => $atribut['prodi'] ?? 'Teknik Informatika',
            'angkatan' => $atribut['angkatan'] ?? 2026,
            'status_kipk' => $atribut['status_kipk'] ?? 'aktif',
        ]);
        $mahasiswa->semester()->create([
            'semester_ke' => 1,
            'ips' => $atribut['ipk'] ?? 3.00,
            'ipk' => $atribut['ipk'] ?? 3.00,
            'sks' => 20,
            'diinput_oleh' => $admin->id,
            'created_at' => now(),
        ]);

        return $mahasiswa;
    }
}
