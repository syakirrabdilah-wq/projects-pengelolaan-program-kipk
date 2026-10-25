<?php

namespace Tests\Feature\Command;

use App\Models\DokumentasiMingguan;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class KirimPengingatDokumentasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_perintah_harian_mengingatkan_mahasiswa_aktif_yang_belum_mengirim_tanpa_duplikasi_harian(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(8, 0));
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_hari', 'nilai' => '4']);
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_jam', 'nilai' => '17:00']);
        $belumKirim = $this->buatMahasiswa();
        $sudahKirim = $this->buatMahasiswa();
        $cuti = $this->buatMahasiswa('cuti');
        $this->buatDokumentasi($sudahKirim);

        Artisan::call('kipk:dokumentasi-pengingat');
        Artisan::call('kipk:dokumentasi-pengingat');

        $this->assertSame(1, Notifikasi::where('user_id', $belumKirim->user_id)->count());
        $this->assertDatabaseMissing('notifikasi', ['user_id' => $sudahKirim->user_id]);
        $this->assertDatabaseMissing('notifikasi', ['user_id' => $cuti->user_id]);
    }

    public function test_perintah_tidak_mengingatkan_pada_hari_lain_atau_setelah_jam_tenggat(): void
    {
        $mahasiswa = $this->buatMahasiswa();
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_hari', 'nilai' => '4']);
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_jam', 'nilai' => '17:00']);

        $this->travelTo(now()->setDate(2026, 10, 30)->setTime(8, 0));
        Artisan::call('kipk:dokumentasi-pengingat');
        $this->assertDatabaseCount('notifikasi', 0);

        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(17, 0));
        Artisan::call('kipk:dokumentasi-pengingat');
        $this->assertDatabaseCount('notifikasi', 0);
        $this->assertDatabaseMissing('notifikasi', ['user_id' => $mahasiswa->user_id]);
    }

    public function test_perintah_terdaftar_di_scheduler_harian(): void
    {
        $terjadwal = collect(Schedule::events())->first(fn ($event) => str_contains($event->command, 'kipk:dokumentasi-pengingat'));

        $this->assertNotNull($terjadwal);
        $this->assertSame('0 0 * * *', $terjadwal->expression);
    }

    private function buatMahasiswa(string $statusKipk = 'aktif'): Mahasiswa
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        return $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2026,
            'status_kipk' => $statusKipk,
        ]);
    }

    private function buatDokumentasi(Mahasiswa $mahasiswa): DokumentasiMingguan
    {
        return $mahasiswa->dokumentasiMingguan()->create([
            'tahun' => now()->isoWeekYear,
            'minggu_ke' => now()->isoWeek,
            'file_foto' => "private/dokumentasi/{$mahasiswa->id}/test.jpg",
            'status_verifikasi' => 'ditolak',
            'created_at' => now(),
        ]);
    }
}
