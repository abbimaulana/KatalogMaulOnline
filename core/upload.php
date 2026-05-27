<?php

declare(strict_types=1);

function handle_image_upload(array $file, ?string $existingPath = null): array
{
    if (empty($file['name'])) {
        return ['path' => $existingPath, 'error' => null];
    }

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => $existingPath, 'error' => 'Upload file gagal.'];
    }

    $maxSize = (int) config('upload.max_size', 2097152);
    if ((int) $file['size'] > $maxSize) {
        return ['path' => $existingPath, 'error' => 'Ukuran file melebihi batas.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = config('upload.allowed_ext', []);
    if (!in_array($ext, $allowed, true)) {
        return ['path' => $existingPath, 'error' => 'Format file tidak diizinkan.'];
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        error_log('Upload image validation failed for file: ' . ($file['name'] ?? 'unknown'));
        return ['path' => $existingPath, 'error' => 'File bukan gambar yang valid.'];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetDir = BASE_PATH . '/uploads';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['path' => $existingPath, 'error' => 'Gagal menyimpan gambar.'];
    }

    if ($existingPath && file_exists(BASE_PATH . '/' . $existingPath)) {
        @unlink(BASE_PATH . '/' . $existingPath);
    }

    return ['path' => 'uploads/' . $filename, 'error' => null];
}
