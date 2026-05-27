<?php

declare(strict_types=1);

function config(string $key = '', $default = null)
{
    global $config;

    if ($key === '') {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

function asset_url(string $path = ''): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function upload_url(string $path = ''): string
{
    return base_url(ltrim($path, '/'));
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path, int $status = 302): void
{
    header('Location: ' . base_url($path), true, $status);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function format_currency(int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function uuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = preg_replace('~[^-\w]+~', '', $text);
    if ($text === '') {
        $text = 'item';
    }
    return $text . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
}

function render(string $view, array $data = []): void
{
    extract($data);
    include BASE_PATH . '/views/' . $view . '.php';
}

function render_error(int $code): void
{
    http_response_code($code);
    include BASE_PATH . '/error/' . $code . '.php';
}

function request_value(string $key, $default = null)
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function build_wa_message(array $order): string
{
    $lines = [
        'Halo Maul Online Shop, saya ingin konfirmasi pesanan:',
        '',
        'Kode Pesanan: ' . ($order['order_code'] ?? $order['public_id']),
        'Kode Item: ' . ($order['product_code'] ?? '-'),
        'Nama Item: ' . ($order['product_name'] ?? '-'),
        'Harga: ' . format_currency((int) ($order['price'] ?? 0)),
        'Jumlah: ' . ($order['quantity'] ?? 1),
        'Total: ' . format_currency((int) ($order['total_price'] ?? 0)),
        '',
        'Nama Pembeli: ' . ($order['buyer_name'] ?? '-'),
        'No. HP: ' . ($order['buyer_phone'] ?? '-'),
        'Alamat: ' . ($order['buyer_address'] ?? '-'),
    ];

    return implode("\n", $lines);
}
