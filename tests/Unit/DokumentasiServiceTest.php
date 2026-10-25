<?php

namespace Tests\Unit;

use App\Models\DokumentasiMingguan;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Services\DokumentasiException;
use App\Services\DokumentasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DokumentasiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_penanda_terlambat_memakai_hari_jam_dan_minggu_iso_dari_pengaturan(): void
    {
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_hari', 'nilai' => '5']);
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_jam', 'nilai' => '17:00']);
        $service = app(DokumentasiService::class);

        $this->assertFalse($service->terlambat($this->dokumentasiDibuat('2026-10-30 16:59:00')));
        $this->assertFalse($service->terlambat($this->dokumentasiDibuat('2026-10-30 17:00:00')));
        $this->assertTrue($service->terlambat($this->dokumentasiDibuat('2026-10-30 17:00:01')));
        $this->assertTrue($service->terlambat($this->dokumentasiDibuat('2026-10-31 09:00:00')));

        Pengaturan::where('kunci', 'dokumentasi_tenggat_hari')->update(['nilai' => '1']);
        Pengaturan::where('kunci', 'dokumentasi_tenggat_jam')->update(['nilai' => '08:00']);
        $this->assertFalse($service->terlambat($this->dokumentasiDibuat('2026-10-26 08:00:00')));
        $this->assertTrue($service->terlambat($this->dokumentasiDibuat('2026-10-26 08:01:00')));
    }

    public function test_tenggat_minggu_iso_mengikuti_pengaturan_dan_zona_waktu_aplikasi(): void
    {
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_hari', 'nilai' => '7']);
        Pengaturan::create(['kunci' => 'dokumentasi_tenggat_jam', 'nilai' => '23:59']);
        $service = app(DokumentasiService::class);

        $this->assertFalse($service->terlambat($this->dokumentasiDibuat('2027-01-03 23:59:00')));
        $this->assertFalse($service->terlambat($this->dokumentasiDibuat('2027-01-04 00:01:00')));
    }

    public function test_gambar_beresolusi_tinggi_ditolak_sebelum_didekode(): void
    {
        $dataHeader = 'IHDR'.pack('NNC5', 4000, 3000, 8, 2, 0, 0, 0);
        $pngDenganDimensiBesar = "\x89PNG\r\n\x1a\n".pack('N', 13).$dataHeader.pack('N', crc32($dataHeader));
        $foto = UploadedFile::fake()->createWithContent('resolusi-besar.png', $pngDenganDimensiBesar);
        $mahasiswa = new Mahasiswa;
        $mahasiswa->id = 1;

        $this->expectException(DokumentasiException::class);
        $this->expectExceptionMessage('Resolusi foto terlalu besar');

        app(DokumentasiService::class)->simpan($mahasiswa, $foto, null);
    }

    private function dokumentasiDibuat(string $tanggal): DokumentasiMingguan
    {
        return new DokumentasiMingguan(['created_at' => $tanggal]);
    }
}
