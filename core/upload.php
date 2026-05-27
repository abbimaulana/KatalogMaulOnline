<?php

declare(strict_types=1);

function handle_image_upload(array $file, ?string $existingPath = null): ?string
{
    if (empty($file['name'])) {
        return $existingPath;
    }

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return $existingPath;
    }

    $maxSize = (int) config('upload.max_size', 2097152);
    if ((int) $file['size'] > $maxSize) {
        return $existingPath;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = config('upload.allowed_ext', []);
    if (!in_array($ext, $allowed, true)) {
        return $existingPath;
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return $existingPath;
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetDir = BASE_PATH . '/uploads';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $existingPath;
    }

    if ($existingPath && file_exists(BASE_PATH . '/' . $existingPath)) {
        @unlink(BASE_PATH . '/' . $existingPath);
    }

    return 'uploads/' . $filename;
}
