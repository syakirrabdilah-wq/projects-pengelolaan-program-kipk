<?php

namespace Tests\Feature\Admin;

use App\Models\AturanPrestasi;
use App\Models\KegiatanPembinaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardScaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_admin_melayani_lima_ratus_mahasiswa_dengan_jumlah_kueri_terbatas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AturanPrestasi::create(['ipk_minimal' => 3.00, 'berlaku_mulai' => today()]);
        $now = now();
        $password = Hash::make('password-uji');
        $usernames = [];
        $nims = [];
        $userRows = [];

        for ($i = 1; $i <= 500; $i++) {
            $usernames[] = sprintf('scale-mhs-%04d', $i);
            $nims[] = sprintf('9000%06d', $i);
            $userRows[] = [
                'username' => end($usernames),
                'password' => $password,
                'role' => 'mahasiswa',
                'aktif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($userRows, 100) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $userIds = DB::table('users')->whereIn('username', $usernames)->orderBy('id')->pluck('id')->all();
        $mahasiswaRows = [];
        foreach ($userIds as $index => $userId) {
            $mahasiswaRows[] = [
                'user_id' => $userId,
                'nim' => $nims[$index],
                'nama' => sprintf('Mahasiswa Uji %04d', $index + 1),
                'prodi' => $index % 2 === 0 ? 'Teknik Informatika' : 'Manajemen',
                'angkatan' => 2025 + ($index % 2),
                'status_kipk' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($mahasiswaRows, 100) as $chunk) {
            DB::table('mahasiswa')->insert($chunk);
        }

        $mahasiswaIds = DB::table('mahasiswa')->whereIn('nim', $nims)->orderBy('id')->pluck('id')->all();
        $semesterRows = [];
        foreach ($mahasiswaIds as $mahasiswaId) {
            $semesterRows[] = [
                'mahasiswa_id' => $mahasiswaId,
                'semester_ke' => 1,
                'ips' => 3.20,
                'ipk' => 3.20,
                'sks' => 20,
                'diinput_oleh' => $admin->id,
                'created_at' => $now,
            ];
        }
        foreach (array_chunk($semesterRows, 100) as $chunk) {
            DB::table('semester_akademik')->insert($chunk);
        }

        $kegiatan = KegiatanPembinaan::create([
            'judul' => 'Pembinaan simulasi skala',
            'tanggal' => today(),
            'absen_buka' => $now->copy()->subHours(3),
            'absen_tutup' => $now->copy()->subHour(),
            'dibuat_oleh' => $admin->id,
        ]);
        $absensiRows = array_map(fn ($mahasiswaId) => [
            'kegiatan_id' => $kegiatan->id,
            'mahasiswa_id' => $mahasiswaId,
            'waktu' => $now,
            'status' => 'hadir',
            'file_foto' => 'private/test.jpg',
        ], $mahasiswaIds);
        foreach (array_chunk($absensiRows, 100) as $chunk) {
            DB::table('absensi')->insert($chunk);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk()
            ->assertViewHas('ringkasan', fn (array $ringkasan) => $ringkasan['total_penerima'] === 500
                && $ringkasan['penerima_aktif'] === 500);
        $this->assertLessThanOrEqual(25, $queryCount, 'Dashboard harus memakai jumlah kueri tetap, bukan satu kueri per mahasiswa.');
    }
}
