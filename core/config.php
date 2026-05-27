<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Maul Online Shop',
        'timezone' => 'Asia/Jakarta',
        'base_url' => '',
        'maintenance' => false,
        'currency' => 'IDR',
    ],
    'db' => [
        'host' => 'localhost',
        'name' => 'maulshop',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'maul_session',
        'csrf_key' => '',
        'cookie_secure' => true,
        'cookie_samesite' => 'Lax',
    ],
    'upload' => [
        'max_size' => 2 * 1024 * 1024,
        'allowed_ext' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
    'bots' => [
        'telegram_token' => '',
        'telegram_chat_id' => '',
        'discord_webhook_url' => '',
    ],
    'payment' => [
        'bank_name' => 'BCA',
        'account_name' => 'Maul Online Shop',
        'account_number' => '1234567890',
        'qris_image' => 'uploads/qris.png',
    ],
    'whatsapp' => [
        'customer_bot' => '6287872369848',
        'admin_number' => '6287864865721',
        'meta_phone_id' => '',
        'meta_token' => '',
    ],
];
