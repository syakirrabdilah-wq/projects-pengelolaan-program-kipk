<?php

namespace Tests\Feature\Admin;

use App\Models\Absensi;
use App\Models\KegiatanPembinaan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KegiatanPembinaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_dapat_membuat_mengubah_dan_menghapus_kegiatan_dengan_pembuat_dari_sesi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buka = now()->addDay()->startOfMinute();
        $data = [
            'judul' => 'Pembinaan akademik',
            'tanggal' => $buka->toDateString(),
            'absen_buka' => $buka->format('Y-m-d\TH:i'),
            'absen_tutup' => $buka->copy()->addHour()->format('Y-m-d\TH:i'),
            'dibuat_oleh' => User::factory()->create(['role' => 'admin'])->id,
        ];

        $this->actingAs($admin)->post(route('admin.kegiatan.store'), $data)
            ->assertRedirect(route('admin.kegiatan.index'));
        $kegiatan = KegiatanPembinaan::firstOrFail();
        $this->assertSame($admin->id, $kegiatan->dibuat_oleh);

        $data['judul'] = 'Pembinaan diperbarui';
        $this->actingAs($admin)->put(route('admin.kegiatan.update', $kegiatan), $data)
            ->assertRedirect(route('admin.kegiatan.show', $kegiatan));
        $this->assertSame('Pembinaan diperbarui', $kegiatan->fresh()->judul);

        $this->actingAs($admin)->delete(route('admin.kegiatan.destroy', $kegiatan))
            ->assertRedirect(route('admin.kegiatan.index'));
        $this->assertDatabaseMissing('kegiatan_pembinaan', ['id' => $kegiatan->id]);
    }

    public function test_tutup_harus_setelah_buka_di_validasi_dan_constraint_database(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buka = now()->addDay()->startOfMinute();

        $this->actingAs($admin)->post(route('admin.kegiatan.store'), [
            'judul' => 'Waktu tidak valid',
            'tanggal' => $buka->toDateString(),
            'absen_buka' => $buka->format('Y-m-d\TH:i'),
            'absen_tutup' => $buka->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('absen_tutup');

        $this->expectException(QueryException::class);
        DB::table('kegiatan_pembinaan')->insert([
            'judul' => 'Lewat validasi aplikasi',
            'tanggal' => $buka->toDateString(),
            'absen_buka' => $buka->format('Y-m-d H:i:s'),
            'absen_tutup' => $buka->copy()->subMinute()->format('Y-m-d H:i:s'),
            'dibuat_oleh' => $admin->id,
        ]);
    }

    public function test_mahasiswa_tidak_dapat_membuka_crud_admin_atau_koreksi_absensi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kegiatan = $this->buatKegiatan($admin);
        $mahasiswa = $this->buatMahasiswa();

        $this->actingAs($mahasiswa->user)->get(route('admin.kegiatan.index'))->assertForbidden();
        $this->actingAs($mahasiswa->user)->post(route('admin.kegiatan.absensi.koreksi', [$kegiatan, $mahasiswa]), ['status' => 'hadir'])
            ->assertForbidden();
        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_rekap_kegiatan_memuat_semua_mahasiswa_aktif_dan_menandai_yang_tanpa_catatan_tidak_hadir(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kegiatan = $this->buatKegiatan($admin);
        $mahasiswa = $this->buatMahasiswa();
        $cuti = $this->buatMahasiswa('cuti');

        $this->actingAs($admin)->get(route('admin.kegiatan.show', $kegiatan))
            ->assertOk()->assertSee($mahasiswa->nama)->assertSee('Tidak hadir')->assertDontSee($cuti->nama);
    }

    public function test_admin_menyetujui_atau_menolak_absensi_tanpa_mengganti_foto_dan_waktu_kiriman(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kegiatan = $this->buatKegiatan($admin);
        $mahasiswa = $this->buatMahasiswa();
        $fileFoto = "private/absensi/{$kegiatan->id}/{$mahasiswa->id}/foto-mahasiswa.jpg";
        Storage::disk('local')->put($fileFoto, 'foto kiriman mahasiswa');
        $waktuKirim = now()->subMinutes(10)->startOfSecond();
        $absensi = Absensi::create([
            'kegiatan_id' => $kegiatan->id,
            'mahasiswa_id' => $mahasiswa->id,
            'waktu' => $waktuKirim,
            'status' => 'alpa',
            'file_foto' => $fileFoto,
        ]);

        $this->actingAs($admin)->post(route('admin.kegiatan.absensi.koreksi', [$kegiatan, $mahasiswa]), [
            'status' => 'hadir',
        ])
            ->assertRedirect();
        $this->assertDatabaseHas('absensi', ['id' => $absensi->id, 'status' => 'hadir', 'file_foto' => $fileFoto]);
        $this->assertSame($waktuKirim->format('Y-m-d H:i:s'), $absensi->fresh()->waktu->format('Y-m-d H:i:s'));
        Storage::disk('local')->assertExists($fileFoto);

        $this->actingAs($admin)->get(route('admin.absensi.foto', $absensi))->assertOk();
        $this->actingAs($mahasiswa->user)->get(route('admin.absensi.foto', $absensi))->assertForbidden();

        $this->actingAs($admin)->post(route('admin.kegiatan.absensi.koreksi', [$kegiatan, $mahasiswa]), [
            'status' => 'alpa',
        ])
            ->assertRedirect();
        $this->assertDatabaseHas('absensi', ['id' => $absensi->id, 'status' => 'alpa', 'file_foto' => $fileFoto]);
        $this->assertDatabaseCount('absensi', 1);
        $this->assertSame($waktuKirim->format('Y-m-d H:i:s'), $absensi->fresh()->waktu->format('Y-m-d H:i:s'));

        $this->actingAs($admin)->post(route('admin.kegiatan.absensi.koreksi', [$kegiatan, $mahasiswa]), [
            'status' => 'belum_hadir',
        ])
            ->assertSessionHasErrors('status');

        $tanpaBukti = $this->buatMahasiswa();
        $this->actingAs($admin)->post(route('admin.kegiatan.absensi.koreksi', [$kegiatan, $tanpaBukti]), [
            'status' => 'hadir',
        ])->assertSessionHasErrors('status');
        $this->assertDatabaseMissing('absensi', ['kegiatan_id' => $kegiatan->id, 'mahasiswa_id' => $tanpaBukti->id]);

        $this->actingAs($admin)->post(route('admin.kegiatan.absensi.koreksi', [$kegiatan, $tanpaBukti]), [
            'status' => 'alpa',
        ])->assertRedirect();
        $this->assertDatabaseHas('absensi', [
            'kegiatan_id' => $kegiatan->id,
            'mahasiswa_id' => $tanpaBukti->id,
            'status' => 'alpa',
            'file_foto' => null,
        ]);
    }

    public function test_rekap_mahasiswa_menampilkan_daftar_kegiatan_dan_persentase(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->buatMahasiswa();
        $hadir = $this->buatKegiatan($admin, now()->subHours(2));
        $tidakHadir = $this->buatKegiatan($admin, now()->subHour());
        Absensi::create([
            'kegiatan_id' => $hadir->id,
            'mahasiswa_id' => $mahasiswa->id,
            'waktu' => now()->subHour(),
            'status' => 'hadir',
        ]);

        $this->actingAs($admin)->get(route('admin.kehadiran.mahasiswa', $mahasiswa))
            ->assertOk()->assertSee($hadir->judul)->assertSee($tidakHadir->judul)->assertSee('50.0%');
    }

    private function buatKegiatan(User $admin, $waktuBuka = null): KegiatanPembinaan
    {
        $waktuBuka ??= now()->addMinutes(5);

        return KegiatanPembinaan::create([
            'judul' => fake()->unique()->sentence(3),
            'tanggal' => $waktuBuka->toDateString(),
            'absen_buka' => $waktuBuka,
            'absen_tutup' => $waktuBuka->copy()->addMinutes(30),
            'dibuat_oleh' => $admin->id,
        ]);
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

}
