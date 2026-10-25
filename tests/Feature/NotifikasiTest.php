<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dan_mahasiswa_melihat_notifikasi_milik_sendiri_dan_jumlah_belum_dibaca(): void
    {
        $pengguna = User::factory()->create(['role' => 'mahasiswa']);
        $pemilikLain = User::factory()->create(['role' => 'admin']);
        $pengguna->notifikasi()->create(['isi' => 'Pesan milik saya', 'sudah_dibaca' => false, 'created_at' => now()]);
        $pengguna->notifikasi()->create(['isi' => 'Pesan lama', 'sudah_dibaca' => true, 'created_at' => now()->subDay()]);
        $pemilikLain->notifikasi()->create(['isi' => 'Pesan rahasia admin', 'sudah_dibaca' => false, 'created_at' => now()]);

        $this->actingAs($pengguna)->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee('Pesan milik saya')
            ->assertSee('Pesan lama')
            ->assertSee('1 belum dibaca')
            ->assertDontSee('Pesan rahasia admin');

        $this->actingAs($pemilikLain)->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee('Pesan rahasia admin')
            ->assertDontSee('Pesan milik saya');
    }

    public function test_pengguna_dapat_menandai_notifikasi_sendiri_tetapi_tidak_milik_orang_lain(): void
    {
        $pengguna = User::factory()->create(['role' => 'admin']);
        $orangLain = User::factory()->create(['role' => 'mahasiswa']);
        $milikSaya = $pengguna->notifikasi()->create(['isi' => 'Pesan', 'sudah_dibaca' => false, 'created_at' => now()]);
        $milikOrangLain = $orangLain->notifikasi()->create(['isi' => 'Rahasia', 'sudah_dibaca' => false, 'created_at' => now()]);

        $this->actingAs($pengguna)->post(route('notifikasi.baca', $milikSaya))
            ->assertRedirect(route('notifikasi.index'));
        $this->assertTrue($milikSaya->fresh()->sudah_dibaca);
        $this->actingAs($pengguna)->post(route('notifikasi.baca', $milikOrangLain))->assertNotFound();
        $this->assertFalse($milikOrangLain->fresh()->sudah_dibaca);
    }

    public function test_halaman_notifikasi_menampilkan_keadaan_kosong(): void
    {
        $pengguna = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($pengguna)->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee('Belum ada notifikasi');
    }
}
