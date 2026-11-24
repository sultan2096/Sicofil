<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Backup de la base PostgreSQL
 */
class BackupDatabase extends Command
{
    protected $signature = 'sicofil:backup 
        {--path= : Dossier de destination} 
        {--compress : Compresser en .gz}
        {--disk= : Disque de stockage (s3, minio, etc.)}
        {--keep= : Conserver N derniers backups}
        {--retention-days= : Conserver N jours de backups}
        {--encrypt : Chiffrer le backup}
        {--encrypt-key= : Clé de chiffrement (override)} 
        {--delete-local : Supprimer la copie locale après upload}';

    protected $description = 'Sauvegarder la base PostgreSQL (pg_dump)';

    public function handle(): int
    {
        try {
            return $this->executeBackup();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->dispatchBackupFailureAlert($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function executeBackup(): int
    {
        $path = $this->option('path') ?: storage_path('app/backups');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $db = config('database.connections.pgsql');
        $filename = 'sicofil_backup_' . Carbon::now()->format('Y-m-d_His') . '.sql';
        $filepath = $path . '/' . $filename;

        $pgDump = $this->findPgDump();
        if (!$pgDump) {
            $this->error('pg_dump non trouvé. Installez PostgreSQL ou ajoutez son dossier bin au PATH.');
            return self::FAILURE;
        }

        $cmd = sprintf(
            '"%s" -h %s -p %s -U %s -d %s -F p -f %s 2>&1',
            $pgDump,
            $db['host'] ?? '127.0.0.1',
            $db['port'] ?? 5432,
            $db['username'] ?? 'postgres',
            $db['database'] ?? 'sicosa_postgis',
            $filepath
        );

        putenv('PGPASSWORD=' . ($db['password'] ?? ''));

        exec($cmd, $output, $code);

        putenv('PGPASSWORD');

        if ($code !== 0 || !file_exists($filepath)) {
            $msg = 'Échec du backup: ' . implode("\n", $output);
            $this->error($msg);
            $this->dispatchBackupFailureAlert($msg);
            return self::FAILURE;
        }

        if ($this->option('compress')) {
            $gz = $filepath . '.gz';
            $fp = gzopen($gz, 'w9');
            gzwrite($fp, file_get_contents($filepath));
            gzclose($fp);
            unlink($filepath);
            $filepath = $gz;
            $filename = basename($filepath);
        }

        if ($this->shouldEncrypt()) {
            $encryptedPath = $filepath . '.enc';
            $this->encryptFile($filepath, $encryptedPath);
            unlink($filepath);
            $filepath = $encryptedPath;
            $filename = basename($filepath);
        }

        $disk = $this->option('disk') ?: env('BACKUP_DISK');
        if (!empty($disk)) {
            $this->uploadBackup($disk, $filepath, $filename);
            $this->pruneRemoteBackups($disk);
            if ($this->option('delete-local') || env('BACKUP_DELETE_LOCAL_AFTER_UPLOAD')) {
                unlink($filepath);
            }
        }

        $this->pruneLocalBackups($path);

        if (file_exists($filepath)) {
            $size = filesize($filepath);
            $this->info("Backup créé: {$filepath} (" . number_format($size / 1024, 1) . " Ko)");
        } else {
            $this->info("Backup créé et uploadé: {$filename}");
        }
        return self::SUCCESS;
    }

    protected function findPgDump(): ?string
    {
        $paths = [
            'pg_dump',
            'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\14\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe',
            'C:\\xampp\\postgresql\\bin\\pg_dump.exe',
            'C:\\PostgreSQL\\16\\bin\\pg_dump.exe',
            'C:\\PostgreSQL\\15\\bin\\pg_dump.exe',
        ];
        foreach ($paths as $p) {
            if ($p === 'pg_dump') {
                exec('where pg_dump 2>nul', $out, $c);
                if ($c === 0 && !empty($out)) return trim($out[0]);
                continue;
            }
            if (file_exists($p)) return $p;
        }
        foreach (glob('C:\\Program Files\\PostgreSQL\\*\\bin\\pg_dump.exe') ?: [] as $p) {
            if (file_exists($p)) return $p;
        }
        return null;
    }

    protected function shouldEncrypt(): bool
    {
        return (bool) $this->option('encrypt') || !empty(env('BACKUP_ENCRYPTION_KEY'));
    }

    protected function encryptFile(string $source, string $destination): void
    {
        $key = $this->option('encrypt-key') ?: env('BACKUP_ENCRYPTION_KEY');
        if (empty($key)) {
            throw new \RuntimeException('Clé de chiffrement manquante.');
        }

        $cipher = env('BACKUP_ENCRYPTION_CIPHER', 'AES-256-CBC');
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivLength);
        $data = file_get_contents($source);
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new \RuntimeException('Échec du chiffrement.');
        }

        $payload = 'ENC1' . $iv . $encrypted;
        file_put_contents($destination, $payload);
    }

    protected function uploadBackup(string $disk, string $filepath, string $filename): void
    {
        $directory = env('BACKUP_REMOTE_DIR', 'backups');
        $this->info("Upload vers le disque '{$disk}'...");
        Storage::disk($disk)->putFileAs($directory, new File($filepath), $filename);
    }

    protected function pruneLocalBackups(string $path): void
    {
        $keep = (int) ($this->option('keep') ?: env('BACKUP_KEEP', 0));
        $days = (int) ($this->option('retention-days') ?: env('BACKUP_RETENTION_DAYS', 0));
        $pattern = $path . DIRECTORY_SEPARATOR . 'sicofil_backup_*';
        $files = glob($pattern) ?: [];
        $this->pruneFiles($files, $keep, $days);
    }

    protected function pruneRemoteBackups(string $disk): void
    {
        $keep = (int) ($this->option('keep') ?: env('BACKUP_KEEP', 0));
        $days = (int) ($this->option('retention-days') ?: env('BACKUP_RETENTION_DAYS', 0));
        $directory = env('BACKUP_REMOTE_DIR', 'backups');
        $files = Storage::disk($disk)->files($directory);
        $targets = array_filter($files, fn ($f) => str_contains($f, 'sicofil_backup_'));
        $this->pruneFiles($targets, $keep, $days, $disk);
    }

    protected function pruneFiles(array $files, int $keep, int $days, ?string $disk = null): void
    {
        if (empty($files) || ($keep <= 0 && $days <= 0)) {
            return;
        }

        $entries = [];
        foreach ($files as $file) {
            $timestamp = $disk
                ? Storage::disk($disk)->lastModified($file)
                : filemtime($file);
            $entries[] = ['path' => $file, 'ts' => $timestamp];
        }

        usort($entries, fn ($a, $b) => $b['ts'] <=> $a['ts']);

        if ($keep > 0 && count($entries) > $keep) {
            $entries = array_slice($entries, $keep);
        } elseif ($keep > 0) {
            $entries = [];
        }

        if ($days > 0) {
            $cutoff = now()->subDays($days)->timestamp;
            $entries = array_filter($entries, fn ($e) => $e['ts'] < $cutoff);
        }

        foreach ($entries as $entry) {
            if ($disk) {
                Storage::disk($disk)->delete($entry['path']);
            } else {
                @unlink($entry['path']);
            }
        }
    }

    protected function dispatchBackupFailureAlert(string $errorMessage): void
    {
        $email = env('BACKUP_ALERT_EMAIL');
        $phone = env('BACKUP_ALERT_PHONE');
        $msg = 'SICOFIL: Échec du backup du ' . now()->format('d/m/Y H:i') . ' - ' . $errorMessage;

        if (!empty($email)) {
            try {
                \Illuminate\Support\Facades\Mail::raw($msg, function ($m) use ($email) {
                    $m->to($email)->subject('[SICOFIL] Alerte : échec backup');
                });
            } catch (\Throwable $e) {
                \Log::warning('Backup alert email failed: ' . $e->getMessage());
            }
        }

        if (!empty($phone)) {
            try {
                \App\Jobs\SendSmsNotification::dispatch($phone, $msg, 'alerte_backup', null);
            } catch (\Throwable $e) {
                \Log::warning('Backup alert SMS failed: ' . $e->getMessage());
            }
            if (config('services.whatsapp.api_key')) {
                try {
                    \App\Jobs\SendWhatsAppNotification::dispatch($phone, $msg, null);
                } catch (\Throwable $e) {
                    \Log::warning('Backup alert WhatsApp failed: ' . $e->getMessage());
                }
            }
        }
    }
}
