<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_tampil_dengan_form_username_dan_csrf(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false);
    }

    public function test_admin_dan_mahasiswa_masuk_ke_dashboard_sesuai_peran(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-uji', 'password' => 'SandiAman123!', 'role' => 'admin', 'aktif' => true,
        ]);
        $mahasiswa = User::factory()->create([
            'username' => 'mhs-uji', 'password' => 'SandiAman123!', 'role' => 'mahasiswa', 'aktif' => true,
        ]);

        $this->post(route('login.store'), ['username' => $admin->username, 'password' => 'SandiAman123!'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('_token');
        $this->assertAuthenticatedAs($admin);

        auth()->logout();
        $this->post(route('login.store'), ['username' => $mahasiswa->username, 'password' => 'SandiAman123!'])
            ->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertAuthenticatedAs($mahasiswa);
    }

    public function test_kredensial_salah_atau_akun_nonaktif_ditolak_dengan_pesan_umum(): void
    {
        User::factory()->create([
            'username' => 'akun-nonaktif', 'password' => 'SandiAman123!', 'role' => 'admin', 'aktif' => false,
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'username' => 'akun-nonaktif', 'password' => 'SandiAman123!',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_sesi_yang_sudah_login_tidak_dapat_dipindahkan_ke_akun_lain(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = User::factory()->create(['username' => 'mhs-lain', 'role' => 'mahasiswa']);

        $this->actingAs($admin)
            ->post(route('login.store'), ['username' => $mahasiswa->username, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_lima_percobaan_gagal_membatasi_percobaan_berikutnya(): void
    {
        RateLimiter::clear('login:127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))->post(route('login.store'), [
                'username' => 'tidak-ada', 'password' => 'kata-sandi-salah',
            ])->assertRedirect(route('login'));
        }

        $this->from(route('login'))->post(route('login.store'), [
            'username' => 'tidak-ada', 'password' => 'kata-sandi-salah',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('username');

        $this->assertSame(5, RateLimiter::attempts('login:127.0.0.1'));
    }

    public function test_logout_mengakhiri_sesi_dan_route_web_mengirim_header_keamanan(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();

        $response = $this->get(route('login'));
        $response
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $this->assertStringContainsString("script-src 'self' https://cdn.jsdelivr.net 'nonce-", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        preg_match("/'nonce-([^']+)'/", $response->headers->get('Content-Security-Policy'), $nonce);
        $this->assertNotEmpty($nonce[1] ?? null);
        $this->assertStringContainsString('nonce="'.$nonce[1].'"', $response->getContent());
    }
}
