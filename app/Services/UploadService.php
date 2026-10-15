<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validated uploads stored outside the web root (storage/uploads) under
 * random names. Files are only served through an authorised controller.
 */
final class UploadService
{
    /**
     * @return array{path: string, original: string, mime: string, size: int}
     */
    public static function store(array $file, string $folder = 'resources'): array
    {
        if (!isset($file['error'], $file['tmp_name'], $file['name'], $file['size']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \DomainException('upload_failed');
        }
        $max = (int) config('security.upload_max_bytes');
        if ((int) $file['size'] <= 0 || (int) $file['size'] > $max) {
            throw new \DomainException('upload_too_large');
        }
        $original = basename(str_replace('\\', '/', (string) $file['name']));
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = (array) config('security.upload_allowed');
        if (!isset($allowed[$extension])) {
            throw new \DomainException('upload_type');
        }
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        if (!in_array($mime, $allowed[$extension], true)) {
            throw new \DomainException('upload_type');
        }
        if (!preg_match('/^[a-z0-9_-]+$/', $folder)) {
            throw new \InvalidArgumentException('Invalid folder.');
        }
        $dir = base_path('storage/uploads/' . $folder);
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new \RuntimeException('Upload directory unavailable.');
        }
        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        $target = $dir . '/' . $name;
        $moved = is_uploaded_file((string) $file['tmp_name'])
            ? move_uploaded_file((string) $file['tmp_name'], $target)
            : (app()->testing && rename((string) $file['tmp_name'], $target));
        if (!$moved) {
            throw new \DomainException('upload_failed');
        }
        chmod($target, 0640);
        $safeOriginal = mb_substr(preg_replace('/[^\w.\- ]/u', '_', $original) ?: 'file.' . $extension, 0, 190);
        return ['path' => $folder . '/' . $name, 'original' => $safeOriginal, 'mime' => $mime, 'size' => (int) $file['size']];
    }

    /**
     * Resolve a stored relative path safely inside storage/uploads.
     */
    public static function absolutePath(string $relative): ?string
    {
        if (!preg_match('#^[a-z0-9_-]+/[a-f0-9]{32}\.[a-z0-9]{2,5}$#', $relative)) {
            return null;
        }
        $base = realpath(base_path('storage/uploads'));
        $path = realpath(base_path('storage/uploads/' . $relative));
        if ($base === false || $path === false || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $path;
    }
}
