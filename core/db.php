<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $config;

    $host = $config['db']['host'] ?? 'localhost';
    $name = $config['db']['name'] ?? '';
    $charset = $config['db']['charset'] ?? 'utf8mb4';
    $user = $config['db']['user'] ?? '';
    $pass = $config['db']['pass'] ?? '';

    $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    return $pdo;
}
