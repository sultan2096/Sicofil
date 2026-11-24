<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Restauration de la base PostgreSQL depuis un backup
 */
class RestoreDatabase extends Command
{
    protected $signature = 'sicofil:restore
        {--file= : Chemin local du fichier backup}
        {--disk= : Disque distant (s3, minio)}
        {--remote-file= : Nom du fichier sur le disque distant}
        {--decrypt-key= : Clé de déchiffrement}
        {--force : Pas de confirmation}';

    protected $description = 'Restaurer la base PostgreSQL depuis un backup (sql, .gz, .enc)';

    public function handle(): int
    {
        $file = $this->option('file');
        $disk = $this->option('disk');
        $remoteFile = $this->option('remote-file');

        if (empty($file) && (empty($disk) || empty($remoteFile))) {
            $this->error('Indiquez --file=chemin OU --disk=... --remote-file=...');
            return self::FAILURE;
        }

        $localPath = $file;
        $tempPath = null;

        if (!empty($disk) && !empty($remoteFile)) {
            $directory = env('BACKUP_REMOTE_DIR', 'backups');
            $remotePath = $directory . '/' . $remoteFile;
            $tempPath = storage_path('app/restore_temp_' . basename($remoteFile));
            $this->info("Téléchargement depuis {$disk}...");
            $contents = Storage::disk($disk)->get($remotePath);
            file_put_contents($tempPath, $contents);
            $localPath = $tempPath;
        }

        if (!file_exists($localPath)) {
            $this->error("Fichier introuvable: {$localPath}");
            if ($tempPath) {
                @unlink($tempPath);
            }
            return self::FAILURE;
        }

        $toRestore = $localPath;

        if (str_ends_with($localPath, '.enc')) {
            $key = $this->option('decrypt-key') ?: env('BACKUP_ENCRYPTION_KEY');
            if (empty($key)) {
                $this->error('Clé de déchiffrement manquante (--decrypt-key ou BACKUP_ENCRYPTION_KEY).');
                if ($tempPath) {
                    @unlink($tempPath);
                }
                return self::FAILURE;
            }
            $decryptedPath = $localPath . '.dec';
            $this->decryptFile($localPath, $decryptedPath, $key);
            $toRestore = $decryptedPath;
        }

        $decompress = false;
        if (str_ends_with($toRestore, '.gz')) {
            $decompress = true;
            $sqlPath = preg_replace('/\.gz$/', '', $toRestore);
            $this->info('Décompression...');
            $gz = gzopen($toRestore, 'rb');
            $fp = fopen($sqlPath, 'wb');
            while (!gzeof($gz)) {
                fwrite($fp, gzread($gz, 8192));
            }
            gzclose($gz);
            fclose($fp);
            $toRestore = $sqlPath;
        }

        if (!$this->option('force') && !$this->confirm('Êtes-vous sûr de vouloir restaurer ? La base sera écrasée.')) {
            $this->cleanup([$toRestore, $tempPath], $decompress ? $localPath : null);
            return self::SUCCESS;
        }

        $psql = $this->findPsql();
        if (!$psql) {
            $this->error('psql non trouvé.');
            $this->cleanup([$toRestore, $tempPath], $decompress ? $localPath : null);
            return self::FAILURE;
        }

        $db = config('database.connections.pgsql');
        $cmd = sprintf(
            '"%s" -h %s -p %s -U %s -d %s -f %s 2>&1',
            $psql,
            $db['host'] ?? '127.0.0.1',
            $db['port'] ?? 5432,
            $db['username'] ?? 'postgres',
            $db['database'] ?? 'sicosa_postgis',
            $toRestore
        );

        putenv('PGPASSWORD=' . ($db['password'] ?? ''));
        exec($cmd, $output, $code);
        putenv('PGPASSWORD');

        $this->cleanup([$toRestore, $tempPath], $decompress ? $localPath : null);

        if ($code !== 0) {
            $this->error('Échec de la restauration: ' . implode("\n", $output));
            return self::FAILURE;
        }

        $this->info('Restauration terminée avec succès.');
        return self::SUCCESS;
    }

    protected function decryptFile(string $source, string $destination, string $key): void
    {
        $data = file_get_contents($source);
        if (strlen($data) < 8 || substr($data, 0, 4) !== 'ENC1') {
            throw new \RuntimeException('Format de fichier chiffré invalide.');
        }
        $cipher = env('BACKUP_ENCRYPTION_CIPHER', 'AES-256-CBC');
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($data, 4, $ivLength);
        $encrypted = substr($data, 4 + $ivLength);
        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            throw new \RuntimeException('Déchiffrement échoué.');
        }
        file_put_contents($destination, $decrypted);
    }

    protected function cleanup(array $paths, ?string $extra = null): void
    {
        foreach ($paths as $p) {
            if ($p && file_exists($p)) {
                @unlink($p);
            }
        }
        if ($extra && file_exists($extra)) {
            @unlink($extra);
        }
    }

    protected function findPsql(): ?string
    {
        $paths = [
            'psql',
            'C:\\Program Files\\PostgreSQL\\16\\bin\\psql.exe',
            'C:\\Program Files\\PostgreSQL\\15\\bin\\psql.exe',
            'C:\\Program Files\\PostgreSQL\\14\\bin\\psql.exe',
            'C:\\Program Files\\PostgreSQL\\17\\bin\\psql.exe',
            'C:\\xampp\\postgresql\\bin\\psql.exe',
        ];
        foreach ($paths as $p) {
            if ($p === 'psql') {
                exec('where psql 2>nul', $out, $c);
                if ($c === 0 && !empty($out)) {
                    return trim($out[0]);
                }
                continue;
            }
            if (file_exists($p)) {
                return $p;
            }
        }
        foreach (glob('C:\\Program Files\\PostgreSQL\\*\\bin\\psql.exe') ?: [] as $p) {
            if (file_exists($p)) {
                return $p;
            }
        }
        return null;
    }
}
