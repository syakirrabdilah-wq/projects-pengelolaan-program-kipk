<?php

namespace Tests\Feature\Admin;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PenerimaTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_ditolak_dari_seluruh_rute_admin_penerima(): void
    {
        $mahasiswa = User::factory()->create();
        $penerimaLain = $this->createPenerima('2025009', 'Mahasiswa Lain', 'Akuntansi', 2025, 'aktif');

        $this->actingAs($mahasiswa)
            ->get(route('admin.penerima.index'))
            ->assertForbidden();

        $this->actingAs($mahasiswa)
            ->post(route('admin.penerima.store'), [])
            ->assertForbidden();

        $this->actingAs($mahasiswa)
            ->post(route('admin.penerima.import'), [])
            ->assertForbidden();

        $this->actingAs($mahasiswa)
            ->get(route('admin.penerima.show', $penerimaLain))
            ->assertForbidden();

        $this->actingAs($mahasiswa)
            ->put(route('admin.penerima.update', $penerimaLain), [])
            ->assertForbidden();

        $this->actingAs($mahasiswa)
            ->delete(route('admin.penerima.destroy', $penerimaLain))
            ->assertForbidden();
    }

    public function test_admin_dapat_membuat_mengubah_dan_menghapus_penerima_dan_akun(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.penerima.store'), [
            'nim' => '2026001',
            'nama' => 'Siti Aminah',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2026,
            'status_kipk' => 'aktif',
            'password' => 'rahasia123',
        ]);

        $mahasiswa = Mahasiswa::where('nim', '2026001')->firstOrFail();
        $akunId = $mahasiswa->user_id;

        $response->assertRedirect(route('admin.penerima.index'));
        $this->assertSame('2026001', $mahasiswa->user->username);
        $this->assertSame('mahasiswa', $mahasiswa->user->role);
        $this->assertTrue(Hash::check('rahasia123', $mahasiswa->user->password));

        $this->actingAs($admin)->put(route('admin.penerima.update', $mahasiswa), [
            'nim' => '2026001',
            'nama' => 'Siti A.',
            'prodi' => 'Informatika',
            'angkatan' => 2026,
            'status_kipk' => 'cuti',
            'password' => '',
        ])->assertRedirect(route('admin.penerima.show', $mahasiswa));

        $this->assertDatabaseHas('mahasiswa', ['id' => $mahasiswa->id, 'nama' => 'Siti A.', 'status_kipk' => 'cuti']);
        $this->assertTrue(Hash::check('rahasia123', $mahasiswa->fresh()->user->password));

        $this->actingAs($admin)->delete(route('admin.penerima.destroy', $mahasiswa))
            ->assertRedirect(route('admin.penerima.index'));

        $this->assertDatabaseMissing('mahasiswa', ['id' => $mahasiswa->id]);
        $this->assertDatabaseMissing('users', ['id' => $akunId]);
    }

    public function test_admin_dapat_mencari_dan_memfilter_penerima(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createPenerima('2024001', 'Dewi Lestari', 'Teknik Informatika', 2024, 'aktif');
        $this->createPenerima('2023001', 'Budi Santoso', 'Akuntansi', 2023, 'cuti');

        $this->actingAs($admin)
            ->get(route('admin.penerima.index', [
                'cari' => 'Dewi',
                'prodi' => 'Teknik Informatika',
                'angkatan' => 2024,
                'status_kipk' => 'aktif',
            ]))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertDontSee('Budi Santoso');
    }

    public function test_daftar_penerima_dapat_dipaginasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        for ($i = 1; $i <= 16; $i++) {
            $this->createPenerima('2026'.str_pad((string) $i, 3, '0', STR_PAD_LEFT), "Mahasiswa $i", 'Teknik Informatika', 2026, 'aktif');
        }

        $this->actingAs($admin)
            ->get(route('admin.penerima.index'))
            ->assertOk()
            ->assertSee('page=2');
    }

    public function test_status_dicabut_mewajibkan_konfirmasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $penerima = $this->createPenerima('2026004', 'Nia Putri', 'Akuntansi', 2026, 'aktif');
        $data = [
            'nim' => $penerima->nim,
            'nama' => $penerima->nama,
            'prodi' => $penerima->prodi,
            'angkatan' => $penerima->angkatan,
            'status_kipk' => 'dicabut',
            'password' => '',
        ];

        $this->actingAs($admin)
            ->put(route('admin.penerima.update', $penerima), $data)
            ->assertSessionHasErrors('konfirmasi_pencabutan');

        $this->assertDatabaseHas('mahasiswa', ['id' => $penerima->id, 'status_kipk' => 'aktif']);

        $this->actingAs($admin)
            ->put(route('admin.penerima.update', $penerima), [...$data, 'konfirmasi_pencabutan' => '1'])
            ->assertRedirect(route('admin.penerima.show', $penerima));

        $this->assertDatabaseHas('mahasiswa', ['id' => $penerima->id, 'status_kipk' => 'dicabut']);
    }

    public function test_impor_menyimpan_baris_valid_dan_melaporkan_galat_per_baris(): void
    {
        config(['imports.penerima_chunk_size' => 2]);
        $admin = User::factory()->create(['role' => 'admin']);
        $csv = implode("\n", [
            'nim,nama,prodi,angkatan,status_kipk,password',
            '2026002,Rina Putri,Teknik Informatika,2026,aktif,rahasia123',
            '2026003,,Akuntansi,2026,aktif,rahasia123',
            '2026004,Nia Putri,Akuntansi,2026,dicabut,rahasia123',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.penerima.import'), [
                'file' => UploadedFile::fake()->createWithContent('penerima.csv', $csv),
            ])
            ->assertRedirect(route('admin.penerima.import-form'))
            ->assertSessionHas('hasilImpor', function (array $hasil): bool {
                return $hasil['berhasil'] === 1
                    && count($hasil['galat']) === 2
                    && $hasil['galat'][0]['baris'] === 3
                    && $hasil['galat'][1]['baris'] === 4;
            });

        $this->assertDatabaseHas('mahasiswa', ['nim' => '2026002', 'nama' => 'Rina Putri']);
        $this->assertDatabaseMissing('mahasiswa', ['nim' => '2026003']);
        $this->assertDatabaseMissing('mahasiswa', ['nim' => '2026004']);
        $akun = Mahasiswa::where('nim', '2026002')->firstOrFail()->user;
        $this->assertTrue(Hash::check('rahasia123', $akun->password));
    }

    private function createPenerima(string $nim, string $nama, string $prodi, int $angkatan, string $status): Mahasiswa
    {
        $user = User::factory()->create(['username' => $nim]);

        return $user->mahasiswa()->create([
            'nim' => $nim,
            'nama' => $nama,
            'prodi' => $prodi,
            'angkatan' => $angkatan,
            'status_kipk' => $status,
        ]);
    }
}
