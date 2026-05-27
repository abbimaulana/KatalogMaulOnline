<?php

declare(strict_types=1);

function count_products(): int
{
    $stmt = db()->query('SELECT COUNT(*) FROM products');
    return (int) $stmt->fetchColumn();
}

function count_orders(): int
{
    $stmt = db()->query('SELECT COUNT(*) FROM orders');
    return (int) $stmt->fetchColumn();
}

function get_products(?int $limit = null): array
{
    $sql = 'SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC';
    if ($limit) {
        $sql .= ' LIMIT ' . (int) $limit;
    }
    $stmt = db()->query($sql);
    return $stmt->fetchAll();
}

function get_all_products(): array
{
    $stmt = db()->query('SELECT * FROM products ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function get_product_by_public_id(string $publicId): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE public_id = ? LIMIT 1');
    $stmt->execute([$publicId]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function get_product_by_code(string $code): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE product_code = ? LIMIT 1');
    $stmt->execute([$code]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function generate_item_code(): string
{
    $prefix = 'ITM-' . date('Ym') . '-';
    $stmt = db()->prepare('SELECT product_code FROM products WHERE product_code LIKE ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $next = 1;
    if ($last) {
        $lastNum = (int) substr($last, -4);
        $next = $lastNum + 1;
    }
    return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

function create_product(array $data): string
{
    $publicId = uuid();
    $code = $data['product_code'] ?? generate_item_code();
    $slug = slugify($data['name'] ?? 'item');

    $stmt = db()->prepare('INSERT INTO products (public_id, product_code, name, slug, description, price, image_path, is_direct_payment, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $publicId,
        $code,
        $data['name'],
        $slug,
        $data['description'],
        $data['price'],
        $data['image_path'],
        $data['is_direct_payment'],
        $data['is_active'] ?? 1,
    ]);

    return $publicId;
}

function update_product(string $publicId, array $data): void
{
    $stmt = db()->prepare('UPDATE products SET name = ?, slug = ?, description = ?, price = ?, image_path = ?, is_direct_payment = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE public_id = ?');
    $stmt->execute([
        $data['name'],
        slugify($data['name']),
        $data['description'],
        $data['price'],
        $data['image_path'],
        $data['is_direct_payment'],
        $data['is_active'] ?? 1,
        $publicId,
    ]);
}

function delete_product(string $publicId): void
{
    $stmt = db()->prepare('DELETE FROM products WHERE public_id = ?');
    $stmt->execute([$publicId]);
}

function list_orders(int $limit = 10): array
{
    $stmt = db()->prepare('SELECT o.*, p.name AS product_name FROM orders o JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function create_order(string $productPublicId, array $buyer): ?array
{
    $product = get_product_by_public_id($productPublicId);
    if (!$product) {
        return null;
    }

    $quantity = max(1, (int) ($buyer['quantity'] ?? 1));
    $total = (int) $product['price'] * $quantity;

    $publicId = uuid();
    $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $stmt = db()->prepare('INSERT INTO orders (public_id, order_code, product_id, buyer_name, buyer_phone, buyer_address, quantity, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $publicId,
        $orderCode,
        $product['id'],
        $buyer['name'],
        $buyer['phone'],
        $buyer['address'],
        $quantity,
        $total,
        'pending',
    ]);

    return get_order_by_public_id($publicId);
}

function get_order_by_public_id(string $publicId): ?array
{
    $stmt = db()->prepare('SELECT o.*, p.name AS product_name, p.product_code, p.price, p.image_path, p.is_direct_payment FROM orders o JOIN products p ON o.product_id = p.id WHERE o.public_id = ? LIMIT 1');
    $stmt->execute([$publicId]);
    $order = $stmt->fetch();
    return $order ?: null;
}

function update_order_status(string $publicId, string $status): void
{
    $stmt = db()->prepare('UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE public_id = ?');
    $stmt->execute([$status, $publicId]);
}

function get_payment_settings(): array
{
    $stmt = db()->query('SELECT * FROM payment_settings ORDER BY id ASC LIMIT 1');
    $settings = $stmt->fetch();

    if (!$settings) {
        $settings = [
            'bank_name' => config('payment.bank_name'),
            'account_name' => config('payment.account_name'),
            'account_number' => config('payment.account_number'),
            'qris_image' => config('payment.qris_image'),
        ];
    }

    return $settings;
}

function save_payment_settings(array $data): void
{
    $existing = get_payment_settings();
    if (!empty($existing['id'])) {
        $stmt = db()->prepare('UPDATE payment_settings SET bank_name = ?, account_name = ?, account_number = ?, qris_image = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([
            $data['bank_name'],
            $data['account_name'],
            $data['account_number'],
            $data['qris_image'],
            $existing['id'],
        ]);
        return;
    }

    $stmt = db()->prepare('INSERT INTO payment_settings (bank_name, account_name, account_number, qris_image) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $data['bank_name'],
        $data['account_name'],
        $data['account_number'],
        $data['qris_image'],
    ]);
}
