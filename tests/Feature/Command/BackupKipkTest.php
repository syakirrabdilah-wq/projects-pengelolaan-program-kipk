<?php

namespace Tests\Feature\Command;

use Tests\TestCase;

class BackupKipkTest extends TestCase
{
    public function test_backup_menolak_kata_sandi_yang_belum_dikonfigurasi_sebelum_membuat_berkas(): void
    {
        config([
            'database.default' => 'mysql',
            'backup_kipk.password' => null,
        ]);

        $this->artisan('kipk:backup')
            ->expectsOutputToContain('KIPK_BACKUP_PASSWORD')
            ->assertFailed();
    }
}
