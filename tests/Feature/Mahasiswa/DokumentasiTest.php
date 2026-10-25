<?php

namespace Tests\Feature\Mahasiswa;

use App\Models\DokumentasiMingguan;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\User;
use App\Services\DokumentasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumentasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_mahasiswa_dapat_mengirim_jpg_dan_png_dengan_nama_sistem_dan_minggu_iso(): void
    {
        $this->travelTo(now()->setDate(2027, 1, 1)->setTime(10, 0));
        $mahasiswa = $this->buatMahasiswa();
        $mahasiswaLain = $this->buatMahasiswa();

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), [
            'file_foto' => UploadedFile::fake()->image('nama-asli.png', 32, 32),
            'keterangan' => 'Kegiatan pembinaan',
            'mahasiswa_id' => $mahasiswaLain->id,
            'tahun' => 2000,
            'minggu_ke' => 1,
        ])->assertRedirect(route('mahasiswa.dokumentasi.index'));

        $dokumentasi = DokumentasiMingguan::firstOrFail();
        $this->assertSame(2026, $dokumentasi->tahun);
        $this->assertSame(53, $dokumentasi->minggu_ke);
        $this->assertSame("private/dokumentasi/{$mahasiswa->id}/2026-53.jpg", $dokumentasi->file_foto);
        $this->assertSame('menunggu', $dokumentasi->status_verifikasi);
        $this->assertSame('Kegiatan pembinaan', $dokumentasi->keterangan);
        Storage::disk('local')->assertExists($dokumentasi->file_foto);
        Storage::disk('public')->assertMissing($dokumentasi->file_foto);
        $this->assertStringContainsString('image/jpeg', Storage::disk('local')->mimeType($dokumentasi->file_foto));
    }

    public function test_file_yang_bukan_jpg_png_asli_dan_file_di_atas_lima_mb_ditolak(): void
    {
        $mahasiswa = $this->buatMahasiswa();

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), [
            'file_foto' => UploadedFile::fake()->createWithContent('foto.jpg', 'teks biasa'),
        ])->assertSessionHasErrors('file_foto');

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), [
            'file_foto' => UploadedFile::fake()->image('besar.png', 10, 10)->size(5121),
        ])->assertSessionHasErrors('file_foto');

        $this->assertDatabaseCount('dokumentasi_mingguan', 0);
    }

    public function test_lebar_foto_yang_melebihi_batas_diperkecil_sebelum_disimpan(): void
    {
        $mahasiswa = $this->buatMahasiswa();

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), [
            'file_foto' => UploadedFile::fake()->image('lebar.png', 2100, 10),
        ])->assertRedirect();

        $dokumentasi = DokumentasiMingguan::firstOrFail();
        $ukuran = getimagesizefromstring(Storage::disk('local')->get($dokumentasi->file_foto));
        $this->assertSame(2000, $ukuran[0]);
        $this->assertSame(10, $ukuran[1]);
    }

    public function test_hanya_satu_kiriman_per_minggu_dan_tahun_minggu_dari_input_diabaikan(): void
    {
        $mahasiswa = $this->buatMahasiswa();
        $data = ['file_foto' => UploadedFile::fake()->image('foto.jpg', 20, 20)];

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), $data)->assertRedirect();
        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), $data)->assertSessionHasErrors('file_foto');

        $this->assertDatabaseCount('dokumentasi_mingguan', 1);
    }

    public function test_form_mempratinjau_foto_dan_menampilkan_status_terlambat(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 26)->setTime(10, 0));
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_hari', 'nilai' => '1']);
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_jam', 'nilai' => '09:00']);
        $mahasiswa = $this->buatMahasiswa();

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), [
            'file_foto' => UploadedFile::fake()->image('foto.png', 32, 32),
        ])->assertRedirect();

        $dokumentasi = DokumentasiMingguan::firstOrFail();
        $this->actingAs($mahasiswa->user)->get(route('mahasiswa.dokumentasi.index'))
            ->assertOk()->assertSee('Terlambat')->assertDontSee('pratinjau-foto');
        $this->assertTrue(app(DokumentasiService::class)->terlambat($dokumentasi));
    }

    public function test_mahasiswa_tidak_dapat_melihat_foto_mahasiswa_lain(): void
    {
        $mahasiswaA = $this->buatMahasiswa();
        $mahasiswaB = $this->buatMahasiswa();
        $dokumentasi = $this->buatDokumentasi($mahasiswaB);
        Storage::disk('local')->put($dokumentasi->file_foto, 'fake jpeg');

        $this->actingAs($mahasiswaA->user)->get(route('mahasiswa.dokumentasi.foto', $dokumentasi))->assertNotFound();
        $this->actingAs($mahasiswaB->user)->get(route('mahasiswa.dokumentasi.foto', $dokumentasi))->assertOk();
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

    private function buatDokumentasi(Mahasiswa $mahasiswa, string $status = 'menunggu'): DokumentasiMingguan
    {
        return $mahasiswa->dokumentasiMingguan()->create([
            'tahun' => now()->isoWeekYear,
            'minggu_ke' => now()->isoWeek,
            'file_foto' => "private/dokumentasi/{$mahasiswa->id}/test.jpg",
            'status_verifikasi' => $status,
            'created_at' => now(),
        ]);
    }
}
