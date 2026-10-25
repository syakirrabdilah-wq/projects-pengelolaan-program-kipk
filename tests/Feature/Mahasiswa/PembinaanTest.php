<?php

namespace Tests\Feature\Mahasiswa;

use App\Models\Absensi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembinaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_absen_sebelum_dibuka_ditolak_dan_tombol_menjelaskan_jam_buka(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->buatMahasiswa();
        $kegiatan = $this->buatKegiatan($admin, now()->addMinutes(5));

        $this->actingAs($mahasiswa->user)->get(route('mahasiswa.pembinaan.index'))
            ->assertOk()->assertSee('disabled')->assertSee('Absen dibuka pukul');
        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan))
            ->assertSessionHasErrors('file_foto');

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan), ['file_foto' => $this->foto()])
            ->assertSessionHasErrors('absensi');
        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_absen_setelah_ditutup_ditolak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->buatMahasiswa();
        $buka = now()->subMinutes(20);
        $kegiatan = $this->buatKegiatan($admin, $buka);
        $this->travelTo($buka->copy()->addMinutes(31));

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan))
            ->assertSessionHasErrors('file_foto');

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan), ['file_foto' => $this->foto()])
            ->assertSessionHasErrors('absensi');
        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_absen_di_dalam_jendela_berhasil_hanya_satu_kali(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->buatMahasiswa();
        $buka = now()->subMinute();
        $kegiatan = $this->buatKegiatan($admin, $buka);
        $this->travelTo($buka);

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan), ['file_foto' => $this->foto()])
            ->assertRedirect()->assertSessionHas('sukses');
        $absensi = Absensi::firstOrFail();
        $this->assertStringStartsWith('private/absensi/', $absensi->file_foto);
        $this->assertNotSame('kegiatan.png', basename($absensi->file_foto));
        Storage::disk('local')->assertExists($absensi->file_foto);
        $this->assertSame($buka->format('Y-m-d H:i:s'), $absensi->waktu->format('Y-m-d H:i:s'));

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan), ['file_foto' => $this->foto()])
            ->assertSessionHasErrors('absensi');
        $this->assertDatabaseCount('absensi', 1);
        $this->assertDatabaseHas('absensi', [
            'kegiatan_id' => $kegiatan->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'hadir',
        ]);
    }

    public function test_mahasiswa_id_selalu_diambil_dari_sesi_dan_rekap_tidak_membocorkan_mahasiswa_lain(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $saya = $this->buatMahasiswa();
        $lain = $this->buatMahasiswa();
        $buka = now()->subMinute();
        $kegiatan = $this->buatKegiatan($admin, $buka);
        $this->travelTo($buka->copy()->addMinute());

        $this->actingAs($saya->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan), [
            'mahasiswa_id' => $lain->id,
            'file_foto' => $this->foto(),
        ])
            ->assertRedirect();
        $this->assertDatabaseHas('absensi', ['kegiatan_id' => $kegiatan->id, 'mahasiswa_id' => $saya->id]);
        $this->assertDatabaseMissing('absensi', ['kegiatan_id' => $kegiatan->id, 'mahasiswa_id' => $lain->id]);
    }

    public function test_halaman_mahasiswa_hanya_menunjukkan_catatan_kehadirannya_sendiri(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $saya = $this->buatMahasiswa();
        $lain = $this->buatMahasiswa();
        $kegiatanSelesai = $this->buatKegiatan($admin, now()->subHours(2));
        Absensi::create([
            'kegiatan_id' => $kegiatanSelesai->id,
            'mahasiswa_id' => $lain->id,
            'waktu' => now()->subHour(),
            'status' => 'hadir',
        ]);
        $this->actingAs($saya->user)->get(route('mahasiswa.pembinaan.index', ['mahasiswa_id' => $lain->id]))
            ->assertOk()->assertSee($kegiatanSelesai->judul)->assertSeeInOrder(['Status:', 'Tidak hadir'])
            ->assertDontSee('status-badge--success');
    }

    public function test_foto_absensi_wajib_valid_dan_hanya_pemilik_yang_dapat_melihatnya(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pemilik = $this->buatMahasiswa();
        $mahasiswaLain = $this->buatMahasiswa();
        $buka = now()->subMinute();
        $kegiatan = $this->buatKegiatan($admin, $buka);
        $this->travelTo($buka);

        $this->actingAs($pemilik->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan))
            ->assertSessionHasErrors('file_foto');
        $this->actingAs($pemilik->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan), [
            'file_foto' => UploadedFile::fake()->create('bukan-gambar.jpg', 10, 'text/plain'),
        ])->assertSessionHasErrors('file_foto');
        $this->assertDatabaseCount('absensi', 0);

        $this->actingAs($pemilik->user)->post(route('mahasiswa.pembinaan.absen', $kegiatan), ['file_foto' => $this->foto()])
            ->assertRedirect();
        $absensi = Absensi::firstOrFail();

        $this->actingAs($pemilik->user)->get(route('mahasiswa.absensi.foto', $absensi))->assertOk();
        $this->actingAs($mahasiswaLain->user)->get(route('mahasiswa.absensi.foto', $absensi))->assertNotFound();
    }

    private function buatKegiatan(User $admin, $waktuBuka): KegiatanPembinaan
    {
        return KegiatanPembinaan::create([
            'judul' => fake()->unique()->sentence(3),
            'tanggal' => $waktuBuka->toDateString(),
            'absen_buka' => $waktuBuka,
            'absen_tutup' => $waktuBuka->copy()->addMinutes(30),
            'dibuat_oleh' => $admin->id,
        ]);
    }

    private function buatMahasiswa(): Mahasiswa
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

    private function foto(): UploadedFile
    {
        return UploadedFile::fake()->image('kegiatan.png', 20, 20);
    }
}
