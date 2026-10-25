<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_riwayat_perubahan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AuditLog::create([
            'user_id' => $admin->id,
            'jenis_data' => 'semester_akademik',
            'data_id' => '42',
            'aksi' => 'diubah',
            'perubahan' => ['ipk' => ['dari' => 2.75, 'menjadi' => 3.10]],
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-log.index'))
            ->assertOk()
            ->assertSee('Audit log')
            ->assertSee($admin->username)
            ->assertSee('42')
            ->assertSee('2.75')
            ->assertSee('3.1');
    }

    public function test_admin_dapat_memfilter_log_berdasarkan_jenis_aksi_id_dan_tanggal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AuditLog::create([
            'user_id' => $admin->id,
            'jenis_data' => 'aturan_prestasi',
            'data_id' => '15',
            'aksi' => 'diubah',
            'perubahan' => ['ipk_minimal' => ['dari' => 3, 'menjadi' => 3.1]],
            'created_at' => today()->setTime(12, 0),
        ]);
        AuditLog::create([
            'user_id' => $admin->id,
            'jenis_data' => 'pengaturan',
            'data_id' => '16',
            'aksi' => 'dibuat',
            'perubahan' => ['nilai' => ['dari' => null, 'menjadi' => '3']],
            'created_at' => today()->subDay()->setTime(12, 0),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-log.index', [
                'jenis_data' => 'aturan_prestasi',
                'aksi' => 'diubah',
                'data_id' => '15',
                'tanggal_dari' => today()->toDateString(),
                'tanggal_sampai' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('15')
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 1 && $logs->first()->data_id === '15');
    }

    public function test_mahasiswa_tidak_dapat_membuka_audit_log(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($mahasiswa)
            ->get(route('admin.audit-log.index'))
            ->assertForbidden();
    }
}
