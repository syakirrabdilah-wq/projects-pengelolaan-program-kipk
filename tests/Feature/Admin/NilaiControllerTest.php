<?php

namespace Tests\Feature\Admin;

use App\Models\AturanPrestasi;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\SemesterAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NilaiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_menyimpan_nilai_dengan_admin_login_dan_khs_di_disk_privat(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $penerima = $this->createMahasiswa();
        $aturan = AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => '2026-01-01']);
        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '3']);

        $response = $this->actingAs($admin)->post(route('admin.penerima.nilai.store', $penerima), [
            'semester_ke' => 1,
            'ips' => 2.90,
            'ipk' => 2.90,
            'sks' => 20,
            'diinput_oleh' => User::factory()->create(['role' => 'admin'])->id,
            'file_khs' => UploadedFile::fake()->createWithContent('khs.pdf', '%PDF-1.4 private student record'),
        ]);

        $semester = SemesterAkademik::where('mahasiswa_id', $penerima->id)->firstOrFail();
        $response->assertRedirect(route('admin.penerima.nilai.index', $penerima));
        $this->assertSame($admin->id, $semester->diinput_oleh);
        $this->assertStringStartsWith("khs/{$penerima->id}/", $semester->file_khs);
        Storage::disk('local')->assertExists($semester->file_khs);
        $this->actingAs($admin)->get(route('admin.nilai.khs', $semester))->assertOk();
        $this->assertDatabaseHas('peringatan', [
            'mahasiswa_id' => $penerima->id,
            'semester_id' => $semester->id,
            'aturan_id' => $aturan->id,
            'tingkat' => 1,
        ]);
    }

    public function test_admin_dapat_mengoreksi_nilai_dan_layanan_menyinkronkan_peringatan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $penerima = $this->createMahasiswa();
        AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => '2026-01-01']);
        Pengaturan::create(['kunci' => 'peringatan_batas_pencabutan', 'nilai' => '3']);
        $semester = $penerima->semester()->create([
            'semester_ke' => 1,
            'ips' => 2.90,
            'ipk' => 2.90,
            'sks' => 20,
            'diinput_oleh' => $admin->id,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->put(route('admin.penerima.nilai.update', [$penerima, $semester]), [
            'semester_ke' => 1,
            'ips' => 3.20,
            'ipk' => 3.20,
            'sks' => 21,
        ])->assertRedirect(route('admin.penerima.nilai.index', $penerima));

        $this->assertDatabaseMissing('peringatan', ['semester_id' => $semester->id]);
    }

    public function test_nilai_di_luar_rentang_dan_semester_ganda_ditolak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $penerima = $this->createMahasiswa();
        $this->actingAs($admin)->post(route('admin.penerima.nilai.store', $penerima), [
            'semester_ke' => 1,
            'ips' => 4.01,
            'ipk' => -0.01,
            'sks' => 20,
        ])->assertSessionHasErrors(['ips', 'ipk']);

        $penerima->semester()->create([
            'semester_ke' => 1,
            'ips' => 3.00,
            'ipk' => 3.00,
            'sks' => 20,
            'diinput_oleh' => $admin->id,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.penerima.nilai.store', $penerima), [
            'semester_ke' => 1,
            'ips' => 3.00,
            'ipk' => 3.00,
            'sks' => 20,
        ])->assertSessionHasErrors('semester_ke');
    }

    public function test_mahasiswa_tidak_dapat_menulis_nilai_dan_tidak_dapat_mengunduh_khs_mahasiswa_lain(): void
    {
        Storage::fake('local');
        $mahasiswaA = $this->createMahasiswa();
        $mahasiswaB = $this->createMahasiswa();
        $admin = User::factory()->create(['role' => 'admin']);
        $semesterB = $mahasiswaB->semester()->create([
            'semester_ke' => 1,
            'ips' => 3.00,
            'ipk' => 3.00,
            'sks' => 20,
            'file_khs' => 'khs/private-b.pdf',
            'diinput_oleh' => $admin->id,
            'created_at' => now(),
        ]);
        Storage::disk('local')->put($semesterB->file_khs, '%PDF-1.4 private');

        $this->actingAs($mahasiswaA->user)
            ->get(route('admin.penerima.nilai.index', $mahasiswaB))
            ->assertForbidden();
        $this->actingAs($mahasiswaA->user)
            ->post(route('admin.penerima.nilai.store', $mahasiswaB), [])
            ->assertForbidden();
        $this->actingAs($mahasiswaA->user)
            ->put(route('admin.penerima.nilai.update', [$mahasiswaB, $semesterB]), [])
            ->assertForbidden();
        $this->actingAs($mahasiswaA->user)
            ->get(route('mahasiswa.khs.download', $semesterB))
            ->assertNotFound();

        $this->actingAs($mahasiswaB->user)
            ->get(route('mahasiswa.khs.download', $semesterB))
            ->assertOk();
    }

    public function test_khs_harus_pdf_dan_maksimal_lima_mb(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $penerima = $this->createMahasiswa();

        $this->actingAs($admin)->post(route('admin.penerima.nilai.store', $penerima), [
            'semester_ke' => 1,
            'ips' => 3.00,
            'ipk' => 3.00,
            'sks' => 20,
            'file_khs' => UploadedFile::fake()->create('bukan-khs.txt', 1, 'text/plain'),
        ])->assertSessionHasErrors('file_khs');

        $this->actingAs($admin)->post(route('admin.penerima.nilai.store', $penerima), [
            'semester_ke' => 1,
            'ips' => 3.00,
            'ipk' => 3.00,
            'sks' => 20,
            'file_khs' => UploadedFile::fake()->create('terlalu-besar.pdf', 5121, 'application/pdf'),
        ])->assertSessionHasErrors('file_khs');
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
}
