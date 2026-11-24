<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service de stockage MinIO (S3-compatible)
 * Photos UC, reçus PDF
 */
class MinIOStorageService
{
    protected string $disk = 'minio';
    protected string $photosPath = 'photos-uc';
    protected string $recusPath = 'recus-pdf';

    public function __construct()
    {
        $this->disk = env('FILESYSTEM_PHOTOS_DISK', env('FILESYSTEM_DISK', 'local'));
    }

    public function storePhoto(UploadedFile $file, int $uniteId, int $communeId): string
    {
        $path = sprintf('%s/commune-%d/unite-%d/%s.%s',
            $this->photosPath,
            $communeId,
            $uniteId,
            Str::uuid(),
            $file->getClientOriginalExtension() ?: 'jpg'
        );
        return Storage::disk($this->disk)->putFileAs(
            dirname($path),
            $file,
            basename($path),
            'public'
        ) ? $path : '';
    }

    public function storeRecu(string $pdfContent, string $reference, int $communeId): string
    {
        $path = sprintf('%s/commune-%d/%s.pdf', $this->recusPath, $communeId, $reference);
        Storage::disk($this->disk)->put($path, $pdfContent, 'private');
        return $path;
    }

    public function getUrl(string $path, bool $temporary = false): string
    {
        if ($temporary) {
            return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes(60));
        }
        return Storage::disk($this->disk)->url($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }
}
