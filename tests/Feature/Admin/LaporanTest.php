<?php

namespace Tests\Feature\Admin;

use App\Exports\KehadiranExport;
use App\Exports\NilaiExport;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_mengunduh_rekap_nilai_dan_kehadiran_dengan_filter_dan_chunk(): void
    {
        $this->travelTo(now()->startOfMinute());
        Excel::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $informatika = $this->mahasiswa($admin, 'Teknik Informatika', 2025);
        $informatika->update(['nama' => '=1+1']);
        $manajemen = $this->mahasiswa($admin, 'Manajemen', 2024);
        $informatika->semester()->create([
            'semester_ke' => 2, 'ips' => 3.20, 'ipk' => 3.10, 'sks' => 21,
            'diinput_oleh' => $admin->id, 'file_khs' => 'private/khs/rahasia.pdf', 'created_at' => now(),
        ]);
        $kegiatan = KegiatanPembinaan::create([
            'judul' => 'Pembinaan Semester', 'tanggal' => today(),
            'absen_buka' => now()->subHours(3), 'absen_tutup' => now()->subHour(),
            'dibuat_oleh' => $admin->id,
        ]);
        $informatika->absensi()->create(['kegiatan_id' => $kegiatan->id, 'waktu' => now()->subHours(2), 'status' => 'hadir']);

        $this->actingAs($admin)->get(route('admin.laporan.excel', ['jenis' => 'nilai', 'prodi' => 'Teknik Informatika', 'angkatan' => 2025, 'semester' => 2]))
            ->assertOk();
        Excel::assertDownloaded('rekap-nilai-'.now()->format('Ymd-His').'.xlsx', function (NilaiExport $export) use ($informatika, $manajemen) {
            $rows = $export->query()->get();
            $this->assertCount(1, $rows);
            $this->assertSame($informatika->nim, $rows->first()->nim);
            $this->assertNotSame($manajemen->nim, $rows->first()->nim);
            $this->assertSame("'=1+1", $export->map($rows->first())[1]);
            $this->assertNotContains('file_khs', $export->headings());
            $this->assertSame(500, $export->chunkSize());

            return true;
        });

        $this->actingAs($admin)->get(route('admin.laporan.excel', 'kehadiran'))->assertOk();
        Excel::assertDownloaded('rekap-kehadiran-'.now()->format('Ymd-His').'.xlsx', function (KehadiranExport $export) use ($informatika) {
            $rows = $export->query()->get();
            $this->assertCount(2, $rows);
            $this->assertSame('hadir', $rows->firstWhere('nim', $informatika->nim)->status_kehadiran);
            $this->assertSame('Tidak hadir', $rows->firstWhere('nim', '!=', $informatika->nim)->status_kehadiran);
            $this->assertSame(500, $export->chunkSize());

            return true;
        });

        $this->actingAs($admin)->get('/admin/laporan/dokumentasi/excel')->assertNotFound();
    }

    public function test_ekspor_menolak_mahasiswa_dan_filter_dengan_format_salah(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($mahasiswa)->get(route('admin.laporan.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.laporan.excel', ['jenis' => 'nilai', 'angkatan' => 'bukan-tahun']))
            ->assertSessionHasErrors('angkatan');
    }

    public function test_admin_dapat_mengunduh_pdf_laporan_semester_dengan_header_tanggal_dan_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->mahasiswa($admin, 'Teknik Informatika', 2025);
        $mahasiswa->semester()->create([
            'semester_ke' => 1, 'ips' => 3.25, 'ipk' => 3.15, 'sks' => 20,
            'diinput_oleh' => $admin->id, 'file_khs' => 'private/khs/rahasia.pdf', 'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.pdf-semester', ['semester' => 1]));
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringNotContainsString('private/khs/rahasia.pdf', $response->getContent());
    }

    private function mahasiswa(User $admin, string $prodi, int $angkatan): Mahasiswa
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        return $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'prodi' => $prodi,
            'angkatan' => $angkatan,
            'status_kipk' => 'aktif',
        ]);
    }
}
