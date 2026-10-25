<?php

namespace Tests\Feature\Admin;

use App\Models\AturanPrestasi;
use App\Models\AuditLog;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_perubahan_nilai_status_aturan_dan_pengaturan_mencatat_admin_field_dan_waktu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $mahasiswa = $this->mahasiswa($admin);
        AuditLog::query()->delete();

        $semester = $mahasiswa->semester()->create([
            'semester_ke' => 1,
            'ips' => 3.00,
            'ipk' => 3.00,
            'sks' => 20,
            'file_khs' => 'private/khs/rahasia.pdf',
            'diinput_oleh' => $admin->id,
            'created_at' => now(),
        ]);
        $semester->update(['ipk' => 2.85, 'file_khs' => 'private/khs/baru-rahasia.pdf']);
        $mahasiswa->update(['status_kipk' => 'cuti']);

        $aturan = AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => today()]);
        $aturan->update(['ipk_minimal' => 3.10]);

        $pengaturan = Pengaturan::create([
            'kunci' => 'peringatan_batas_pencabutan',
            'nilai' => '3',
            'diubah_oleh' => $admin->id,
        ]);
        $pengaturan->update(['nilai' => '4', 'diubah_oleh' => $admin->id]);

        $logs = AuditLog::query()->with('pengguna')->orderBy('id')->get();
        $this->assertCount(7, $logs);
        $this->assertTrue($logs->every(fn (AuditLog $log) => $log->user_id === $admin->id && $log->pengguna?->isAdmin()));
        $this->assertTrue($logs->every(fn (AuditLog $log) => $log->created_at !== null));

        $nilaiDiperbarui = $logs->first(fn (AuditLog $log) => $log->jenis_data === 'semester_akademik' && $log->aksi === 'diubah');
        $this->assertSame(['ipk'], array_keys($nilaiDiperbarui->perubahan));
        $this->assertEquals(3, $nilaiDiperbarui->perubahan['ipk']['dari']);
        $this->assertEquals(2.85, $nilaiDiperbarui->perubahan['ipk']['menjadi']);
        $this->assertStringNotContainsString('private/khs', json_encode($logs->toArray()));
        $this->assertStringNotContainsString($mahasiswa->nim, json_encode($logs->toArray()));

        $status = $logs->first(fn (AuditLog $log) => $log->jenis_data === 'mahasiswa' && $log->aksi === 'diubah');
        $this->assertSame(['status_kipk'], array_keys($status->perubahan));
        $this->assertSame('aktif', $status->perubahan['status_kipk']['dari']);
        $this->assertSame('cuti', $status->perubahan['status_kipk']['menjadi']);
        $this->assertTrue($logs->contains(fn (AuditLog $log) => $log->jenis_data === 'aturan_prestasi'));
        $this->assertTrue($logs->contains(fn (AuditLog $log) => $log->jenis_data === 'pengaturan' && $log->perubahan['nilai']['menjadi'] === '4'));
    }

    public function test_log_menyimpan_pembuatan_dan_penghapusan_aturan_tanpa_konten_tambahan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $aturan = AturanPrestasi::create(['ipk_minimal' => 2.90, 'berlaku_mulai' => today()->addYear()]);
        $aturan->delete();

        $this->assertDatabaseHas('audit_log', [
            'user_id' => $admin->id,
            'jenis_data' => 'aturan_prestasi',
            'data_id' => (string) $aturan->id,
            'aksi' => 'dihapus',
        ]);
    }

    public function test_perubahan_tanpa_admin_login_tidak_mencatat_aktor_palsu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = $this->mahasiswa($admin);
        AuditLog::query()->delete();

        $this->actingAs($mahasiswa->user)->get('/');
        $mahasiswa->update(['status_kipk' => 'cuti']);

        $this->assertDatabaseCount('audit_log', 0);
    }

    private function mahasiswa(User $admin): Mahasiswa
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        return $user->mahasiswa()->create([
            'nim' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2025,
            'status_kipk' => 'aktif',
        ]);
    }
}
