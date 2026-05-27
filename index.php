<?php

declare(strict_types=1);

require __DIR__ . '/core/init.php';

if (config('app.maintenance')) {
    http_response_code(503);
    include __DIR__ . '/maintenance.php';
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($path ?? '', '/');

if (str_starts_with($path, 'admin')) {
    include __DIR__ . '/core/admin_router.php';
    exit;
}

switch ($path) {
    case '':
    case 'home':
        $products = get_products(6);
        render('home', ['pageTitle' => 'Beranda', 'products' => $products]);
        break;
    case 'catalog':
        $products = get_products();
        render('catalog', ['pageTitle' => 'Katalog', 'products' => $products]);
        break;
    case 'about':
        render('about', ['pageTitle' => 'Tentang Kami']);
        break;
    case 'contact':
        render('contact', ['pageTitle' => 'Kontak']);
        break;
    case 'checkout':
        if (!is_post() || !verify_csrf()) {
            set_flash('error', 'Permintaan tidak valid.');
            redirect('catalog');
        }

        $productId = sanitize_text((string) request_value('product_id'));
        $buyer = [
            'name' => sanitize_text((string) request_value('buyer_name')),
            'phone' => sanitize_phone((string) request_value('buyer_phone')),
            'address' => sanitize_text((string) request_value('buyer_address')),
            'quantity' => (int) request_value('quantity', 1),
        ];

        if ($buyer['name'] === '' || $buyer['phone'] === '') {
            set_flash('error', 'Nama dan nomor telepon wajib diisi.');
            redirect('catalog');
        }

        $order = create_order($productId, $buyer);
        if (!$order) {
            set_flash('error', 'Produk tidak ditemukan.');
            redirect('catalog');
        }

        $target = $order['is_direct_payment'] ? 'checkout_payment' : 'checkout_confirm';
        redirect($target . '?order=' . $order['public_id']);
        break;
    case 'checkout_payment':
        $orderId = sanitize_text((string) request_value('order'));
        $order = $orderId ? get_order_by_public_id($orderId) : null;
        if (!$order) {
            render_error(404);
            break;
        }
        $payment = get_payment_settings();
        render('checkout_payment', ['pageTitle' => 'Pembayaran', 'order' => $order, 'payment' => $payment]);
        break;
    case 'checkout_confirm':
        $orderId = sanitize_text((string) request_value('order'));
        $order = $orderId ? get_order_by_public_id($orderId) : null;
        if (!$order) {
            render_error(404);
            break;
        }
        render('checkout_confirm', ['pageTitle' => 'Konfirmasi Pesanan', 'order' => $order]);
        break;
    case 'confirm':
        if (!is_post() || !verify_csrf()) {
            set_flash('error', 'Token tidak valid.');
            redirect('catalog');
        }
        $orderId = sanitize_text((string) request_value('order_id'));
        $order = $orderId ? get_order_by_public_id($orderId) : null;
        if (!$order) {
            render_error(404);
            break;
        }
        $status = $order['is_direct_payment'] ? 'paid' : 'confirmed';
        update_order_status($orderId, $status);
        notify_order($order);

        $waNumber = config('whatsapp.customer_bot');
        $message = urlencode(build_wa_message($order));
        header('Location: https://wa.me/' . $waNumber . '?text=' . $message, true, 302);
        exit;
    default:
        render_error(404);
        break;
}
