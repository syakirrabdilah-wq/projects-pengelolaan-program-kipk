<?php

namespace Tests\Feature\Admin;

use App\Models\DokumentasiMingguan;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\User;
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
    }

    public function test_admin_memverifikasi_dokumentasi_mencatat_admin_dan_mengirim_notifikasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->buatMahasiswa();
        $dokumentasi = $this->buatDokumentasi($mahasiswa);
        Storage::disk('local')->put($dokumentasi->file_foto, 'fake jpeg');

        $this->actingAs($admin)->get(route('admin.dokumentasi.index'))
            ->assertOk()->assertSee($mahasiswa->nama);
        $this->actingAs($admin)->get(route('admin.dokumentasi.show', $dokumentasi))
            ->assertOk()->assertSee(route('admin.dokumentasi.foto', $dokumentasi));
        $this->actingAs($admin)->post(route('admin.dokumentasi.verifikasi', $dokumentasi), [
            'status_verifikasi' => 'diterima',
            'catatan_admin' => 'Foto jelas',
        ])->assertRedirect(route('admin.dokumentasi.index'));

        $this->assertDatabaseHas('dokumentasi_mingguan', [
            'id' => $dokumentasi->id,
            'status_verifikasi' => 'diterima',
            'catatan_admin' => 'Foto jelas',
            'diverifikasi_oleh' => $admin->id,
        ]);
        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $mahasiswa->user_id,
            'isi' => "Dokumentasi minggu ke-{$dokumentasi->minggu_ke} ({$dokumentasi->tahun}) diterima.",
        ]);
        $this->actingAs($admin)->post(route('admin.dokumentasi.verifikasi', $dokumentasi), [
            'status_verifikasi' => 'diterima',
            'catatan_admin' => 'Sudah diperiksa',
        ])->assertNotFound();
        $this->assertSame(1, Notifikasi::where('user_id', $mahasiswa->user_id)->count());
    }

    public function test_keputusan_verifikasi_mewajibkan_catatan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dokumentasi = $this->buatDokumentasi($this->buatMahasiswa());

        $this->actingAs($admin)->post(route('admin.dokumentasi.verifikasi', $dokumentasi), [
            'status_verifikasi' => 'ditolak',
        ])->assertSessionHasErrors('catatan_admin');
        $this->assertSame('menunggu', $dokumentasi->fresh()->status_verifikasi);
    }

    public function test_tolak_dengan_catatan_mengizinkan_kiriman_ulang_minggu_ini(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->buatMahasiswa();
        $dokumentasi = $this->buatDokumentasi($mahasiswa);
        Storage::disk('local')->put($dokumentasi->file_foto, 'old image');

        $this->actingAs($admin)->post(route('admin.dokumentasi.verifikasi', $dokumentasi), [
            'status_verifikasi' => 'ditolak',
            'catatan_admin' => 'Foto belum jelas',
        ])->assertRedirect();
        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $mahasiswa->user_id,
            'isi' => 'Dokumentasi ditolak. Catatan: Foto belum jelas',
        ]);

        $this->actingAs($mahasiswa->user)->post(route('mahasiswa.dokumentasi.store'), [
            'file_foto' => UploadedFile::fake()->image('foto-baru.png', 40, 40),
            'keterangan' => 'Kirim ulang',
        ])->assertRedirect();

        $this->assertDatabaseCount('dokumentasi_mingguan', 1);
        $this->assertDatabaseHas('dokumentasi_mingguan', [
            'id' => $dokumentasi->id,
            'status_verifikasi' => 'menunggu',
            'catatan_admin' => null,
            'diverifikasi_oleh' => null,
            'keterangan' => 'Kirim ulang',
        ]);
    }

    public function test_mahasiswa_ditolak_dari_antrean_verifikasi_dan_tidak_dapat_mengunduh_foto_orang_lain(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswaA = $this->buatMahasiswa();
        $mahasiswaB = $this->buatMahasiswa();
        $dokumentasi = $this->buatDokumentasi($mahasiswaB);
        Storage::disk('local')->put($dokumentasi->file_foto, 'fake jpeg');

        $this->actingAs($mahasiswaA->user)->get(route('admin.dokumentasi.index'))->assertForbidden();
        $this->actingAs($mahasiswaA->user)->get(route('admin.dokumentasi.foto', $dokumentasi))->assertForbidden();
        $this->actingAs($mahasiswaA->user)->post(route('admin.dokumentasi.verifikasi', $dokumentasi), [
            'status_verifikasi' => 'diterima',
        ])->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dokumentasi.foto', $dokumentasi))->assertOk();
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

    private function buatDokumentasi(Mahasiswa $mahasiswa): DokumentasiMingguan
    {
        return $mahasiswa->dokumentasiMingguan()->create([
            'tahun' => now()->isoWeekYear,
            'minggu_ke' => now()->isoWeek,
            'file_foto' => "private/dokumentasi/{$mahasiswa->id}/test.jpg",
            'status_verifikasi' => 'menunggu',
            'created_at' => now(),
        ]);
    }
}
