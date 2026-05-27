<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_token'])) {
        $seed = bin2hex(random_bytes(32));
        $key = (string) config('security.csrf_key', '');
        if ($key === '') {
            $key = bin2hex(random_bytes(32));
        }
        $_SESSION['_token'] = hash_hmac('sha256', $seed, $key);
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
