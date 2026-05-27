<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['_token'] ?? '';
    if ($token === '' || empty($_SESSION['_token'])) {
        return false;
    }
    return hash_equals($_SESSION['_token'], $token);
}

function sanitize_text(string $value): string
{
    return trim(strip_tags($value));
}

function sanitize_phone(string $value): string
{
    return preg_replace('/[^0-9+]/', '', $value);
}
