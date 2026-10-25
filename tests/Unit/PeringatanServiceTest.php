<?php

namespace Tests\Unit;

use App\Models\AturanPrestasi;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\Pengaturan;
use App\Models\SemesterAkademik;
use App\Models\User;
use App\Services\PeringatanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeringatanServiceTest extends TestCase
{
    use RefreshDatabase;

    private PeringatanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => '2026-01-01']);
        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '3']);
        $this->service = app(PeringatanService::class);
    }

    public function test_satu_ipk_di_bawah_batas_menghasilkan_tingkat_satu(): void
    {
        $mahasiswa = $this->createMahasiswa();
        $this->createSemester($mahasiswa, 1, 2.90);

        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertSame([1], $warnings->pluck('tingkat')->all());
    }

    public function test_dua_semester_berturut_turut_di_bawah_batas_menghasilkan_tingkat_satu_dan_dua(): void
    {
        $mahasiswa = $this->mahasiswaDenganIpk([2.90, 2.95]);

        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertSame([1, 2], $warnings->pluck('tingkat')->all());
    }

    public function test_tiga_semester_berturut_turut_di_bawah_batas_menghasilkan_tingkat_satu_sampai_tiga_tanpa_mencabut_status(): void
    {
        $mahasiswa = $this->mahasiswaDenganIpk([2.90, 2.95, 2.80]);

        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertSame([1, 2, 3], $warnings->pluck('tingkat')->all());
        $this->assertTrue($warnings->last()->memenuhi_batas_pencabutan);
        $this->assertSame('aktif', $mahasiswa->fresh()->status_kipk);
    }

    public function test_ipk_yang_memenuhi_batas_mereset_rangkaian(): void
    {
        $mahasiswa = $this->mahasiswaDenganIpk([2.90, 3.10, 2.85]);

        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertSame([1, 1], $warnings->pluck('tingkat')->all());
        $this->assertDatabaseMissing('peringatan', ['mahasiswa_id' => $mahasiswa->id, 'semester_id' => 2]);
    }

    public function test_ipk_tepat_pada_batas_tidak_menghasilkan_peringatan(): void
    {
        $mahasiswa = $this->mahasiswaDenganIpk([3.00]);

        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertCount(0, $warnings);
        $this->assertDatabaseCount('peringatan', 0);
    }

    public function test_semester_yang_tidak_memiliki_data_memutus_rangkaian(): void
    {
        $mahasiswa = $this->createMahasiswa();
        $this->createSemester($mahasiswa, 1, 2.90);
        $this->createSemester($mahasiswa, 3, 2.80);

        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertSame([1, 1], $warnings->pluck('tingkat')->all());
    }

    public function test_koreksi_ipk_menghapus_peringatan_dan_menghitung_ulang_tingkat_berikutnya(): void
    {
        $mahasiswa = $this->mahasiswaDenganIpk([2.90, 2.95, 2.80]);
        $this->service->evaluasi($mahasiswa);

        $semesterDua = $mahasiswa->semester()->where('semester_ke', 2)->firstOrFail();
        $semesterDua->update(['ipk' => 3.20]);
        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertDatabaseMissing('peringatan', ['mahasiswa_id' => $mahasiswa->id, 'semester_id' => $semesterDua->id]);
        $this->assertDatabaseHas('peringatan', ['mahasiswa_id' => $mahasiswa->id, 'semester_id' => 3, 'tingkat' => 1]);
        $this->assertSame([1, 1], $warnings->pluck('tingkat')->all());
    }

    public function test_menggunakan_aturan_terbaru_yang_sudah_berlaku_pada_tanggal_input(): void
    {
        $mahasiswa = $this->createMahasiswa();
        AturanPrestasi::create(['ipk_minimal' => 2.80, 'berlaku_mulai' => '2025-01-01']);
        AturanPrestasi::create(['ipk_minimal' => 2.80, 'berlaku_mulai' => '2026-08-01']);
        AturanPrestasi::create(['ipk_minimal' => 3.10, 'berlaku_mulai' => '2026-09-01']);
        $this->createSemester($mahasiswa, 1, 2.90, '2026-08-31');
        $this->createSemester($mahasiswa, 2, 2.90, '2026-09-01');

        $warnings = $this->service->evaluasi($mahasiswa);

        $this->assertSame([1], $warnings->pluck('tingkat')->all());
        $this->assertSame('2026-09-01', $warnings->first()->aturan->berlaku_mulai->format('Y-m-d'));
    }

    public function test_batas_pencabutan_dibaca_dari_pengaturan_dengan_default_tiga(): void
    {
        Pengaturan::where('kunci', 'peringatan_batas_pencabutan')->delete();
        $this->assertSame(3, $this->service->batasPencabutan());

        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '4']);

        $this->assertSame(4, $this->service->batasPencabutan());
    }

    public function test_peringatan_baru_di_bawah_ambang_hanya_memberi_notifikasi_ke_mahasiswa(): void
    {
        $mahasiswa = $this->mahasiswaDenganIpk([2.90]);

        $this->service->evaluasi($mahasiswa);

        $this->assertSame(1, Notifikasi::where('user_id', $mahasiswa->user_id)->count());
        $this->assertSame(0, Notifikasi::whereIn('user_id', User::where('role', 'admin')->pluck('id'))->count());
    }

    public function test_tingkat_pencabutan_memberi_notifikasi_ke_mahasiswa_dan_semua_admin_tanpa_duplikasi(): void
    {
        $mahasiswa = $this->mahasiswaDenganIpk([2.90, 2.95, 2.80]);
        $adminIds = User::where('role', 'admin')->pluck('id');

        $this->service->evaluasi($mahasiswa);
        $jumlahNotifikasi = Notifikasi::count();
        $this->service->evaluasi($mahasiswa);

        $this->assertSame($jumlahNotifikasi, Notifikasi::count());
        $this->assertSame($adminIds->count(), Notifikasi::whereIn('user_id', $adminIds)->count());
        $this->assertSame(3, Notifikasi::where('user_id', $mahasiswa->user_id)->count());
        $this->assertSame('aktif', $mahasiswa->fresh()->status_kipk);
        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $mahasiswa->user_id,
            'isi' => 'Peringatan IPK tingkat 3: Anda memenuhi syarat pencabutan biaya KIP-K. Admin akan meninjau status Anda.',
        ]);
    }

    private function mahasiswaDenganIpk(array $ipk): Mahasiswa
    {
        $mahasiswa = $this->createMahasiswa();

        foreach ($ipk as $index => $nilai) {
            $this->createSemester($mahasiswa, $index + 1, $nilai);
        }

        return $mahasiswa;
    }

    private function createMahasiswa(): Mahasiswa
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        return $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2026,
            'status_kipk' => 'aktif',
        ]);
    }

    private function createSemester(Mahasiswa $mahasiswa, int $semesterKe, float $ipk, string $tanggalInput = '2026-10-25'): SemesterAkademik
    {
        $admin = User::factory()->create(['role' => 'admin']);

        return $mahasiswa->semester()->create([
            'semester_ke' => $semesterKe,
            'ips' => $ipk,
            'ipk' => $ipk,
            'sks' => 20,
            'diinput_oleh' => $admin->id,
            'created_at' => $tanggalInput,
        ]);
    }
}
