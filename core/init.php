<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/core/config.php';

if (!empty($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}

$cookieSameSite = $config['security']['cookie_samesite'] ?? 'Lax';

$scheme = 'http';
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $scheme = 'https';
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
} elseif (!empty($_SERVER['HTTP_CF_VISITOR'])) {
    $cf = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
    if (!empty($cf['scheme'])) {
        $scheme = $cf['scheme'];
    }
}
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = trim($config['app']['base_url'] ?? '');
if ($baseUrl === '') {
    $baseUrl = $scheme . '://' . $host;
}

define('BASE_URL', rtrim($baseUrl, '/'));

$cookieSecure = (bool) ($config['security']['cookie_secure'] ?? false);
$cookieSecure = $cookieSecure && $scheme === 'https';

if (($config['security']['csrf_key'] ?? '') === '') {
    error_log('SECURITY WARNING: security.csrf_key is empty. Set a strong random key in core/config.php.');
}

if (($config['db']['pass'] ?? '') === '') {
    error_log('SECURITY WARNING: Database password is empty. Update core/config.php for production.');
}

if (($config['security']['cookie_secure'] ?? false) && $scheme !== 'https') {
    error_log('SECURITY WARNING: HTTPS not detected, secure cookies disabled for this request.');
}

session_name($config['security']['session_name'] ?? 'maul_session');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $cookieSecure,
        'httponly' => true,
        'samesite' => $cookieSameSite,
    ]);
    session_start();
}

require BASE_PATH . '/core/db.php';
require BASE_PATH . '/core/helpers.php';
require BASE_PATH . '/core/security.php';
require BASE_PATH . '/core/store.php';
require BASE_PATH . '/core/upload.php';
require BASE_PATH . '/core/webhooks.php';
require BASE_PATH . '/core/auth.php';
