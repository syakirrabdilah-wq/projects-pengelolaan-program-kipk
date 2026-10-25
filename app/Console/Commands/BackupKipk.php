<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class BackupKipk extends Command
{
    protected $signature = 'kipk:backup';

    protected $description = 'Buat backup database dan berkas privat terenkripsi.';

    public function handle(Filesystem $filesystem): int
    {
        if (config('database.default') !== 'mysql') {
            $this->components->error('Backup KIP-K hanya mendukung koneksi database MySQL.');

            return self::FAILURE;
        }

        $password = config('backup_kipk.password');
        if (! is_string($password) || strlen($password) < 32) {
            $this->components->error('Isi KIPK_BACKUP_PASSWORD dengan rahasia acak minimal 32 karakter.');

            return self::FAILURE;
        }

        if (! class_exists(ZipArchive::class) || ! defined('ZipArchive::EM_AES_256') || ! method_exists(ZipArchive::class, 'setEncryptionName')) {
            $this->components->error('PHP ZipArchive dengan enkripsi AES-256 tidak tersedia.');

            return self::FAILURE;
        }

        $destination = rtrim((string) config('backup_kipk.destination'), DIRECTORY_SEPARATOR);
        $filesystem->ensureDirectoryExists($destination, 0700, true);
        $destination = realpath($destination) ?: $destination;
        $public = realpath(public_path());
        $private = realpath(Storage::disk('local')->path(''));

        if ($this->isWithin($destination, $public) || $this->isWithin($destination, $private)) {
            $this->components->error('Lokasi backup harus berada di luar folder publik dan folder berkas privat.');

            return self::FAILURE;
        }

        $database = config('database.connections.mysql');
        $temporaryDirectory = storage_path('app/tmp-backup');
        $filesystem->ensureDirectoryExists($temporaryDirectory, 0700, true);
        $dumpPath = $temporaryDirectory.DIRECTORY_SEPARATOR.'kipk-'.bin2hex(random_bytes(8)).'.sql';
        $archivePath = $destination.DIRECTORY_SEPARATOR.'kipk-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3)).'.zip';
        $zip = null;

        try {
            $process = new Process([
                (string) config('backup_kipk.mysqldump_binary'),
                '--single-transaction',
                '--skip-lock-tables',
                '--routines',
                '--triggers',
                '--default-character-set=utf8mb4',
                '--host='.(string) ($database['host'] ?? '127.0.0.1'),
                '--port='.(string) ($database['port'] ?? 3306),
                '--user='.(string) ($database['username'] ?? ''),
                '--result-file='.$dumpPath,
                (string) ($database['database'] ?? ''),
            ], base_path(), ['MYSQL_PWD' => (string) ($database['password'] ?? '')], null, 7200);
            $process->mustRun();

            if (! is_file($dumpPath) || filesize($dumpPath) === 0) {
                throw new \RuntimeException('mysqldump tidak menghasilkan berkas database.');
            }

            $zip = new ZipArchive;
            if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new \RuntimeException('Arsip backup tidak dapat dibuat.');
            }
            $zip->setPassword($password);
            $this->addEncryptedFile($zip, $dumpPath, 'database.sql');

            $jumlahBerkas = 0;
            foreach ($filesystem->allFiles(Storage::disk('local')->path('')) as $file) {
                if ($file->isLink()) {
                    continue;
                }

                $relativePath = 'private/'.str_replace('\\', '/', $file->getRelativePathname());
                $this->addEncryptedFile($zip, $file->getRealPath(), $relativePath);
                $jumlahBerkas++;
            }

            $manifest = json_encode([
                'created_at' => now()->toIso8601String(),
                'database' => 'database.sql',
                'private_file_count' => $jumlahBerkas,
                'application_timezone' => config('app.timezone'),
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            if (! $zip->addFromString('manifest.json', $manifest) || ! $zip->setEncryptionName('manifest.json', ZipArchive::EM_AES_256)) {
                throw new \RuntimeException('Manifest backup tidak dapat dienkripsi.');
            }

            if (! $zip->close()) {
                throw new \RuntimeException('Arsip backup tidak dapat diselesaikan.');
            }
            $zip = null;
            @chmod($archivePath, 0600);
            $this->components->info('Backup terenkripsi selesai: '.basename($archivePath));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            if ($zip instanceof ZipArchive) {
                $zip->close();
            }
            if (is_file($archivePath)) {
                @unlink($archivePath);
            }

            report($exception);
            $this->components->error('Backup gagal. Periksa log aplikasi dan konfigurasi mysqldump.');

            return self::FAILURE;
        } finally {
            if (is_file($dumpPath)) {
                @unlink($dumpPath);
            }
        }
    }

    private function addEncryptedFile(ZipArchive $zip, string|false $path, string $archiveName): void
    {
        if (! $path || ! $zip->addFile($path, $archiveName) || ! $zip->setEncryptionName($archiveName, ZipArchive::EM_AES_256)) {
            throw new \RuntimeException('Berkas backup tidak dapat ditambahkan dengan enkripsi.');
        }
    }

    private function isWithin(string $path, string|false $parent): bool
    {
        if (! $parent) {
            return false;
        }

        $path = strtolower(rtrim($path, DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR;
        $parent = strtolower(rtrim($parent, DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR;

        return str_starts_with($path, $parent);
    }
}
