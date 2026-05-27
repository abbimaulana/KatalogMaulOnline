<?php

declare(strict_types=1);

$adminPath = trim(substr($path, strlen('admin')), '/');

if ($adminPath === 'login') {
    include BASE_PATH . '/admin/login.php';
    return;
}

if ($adminPath === 'logout') {
    logout_admin();
    set_flash('success', 'Berhasil logout.');
    redirect('admin/login');
}

require_admin();

switch ($adminPath) {
    case '':
    case 'dashboard':
        include BASE_PATH . '/admin/dashboard.php';
        break;
    case 'products':
        include BASE_PATH . '/admin/products.php';
        break;
    case 'products/create':
    case 'products/edit':
        include BASE_PATH . '/admin/product_form.php';
        break;
    case 'products/export':
        include BASE_PATH . '/admin/products_export.php';
        break;
    case 'products/import':
        include BASE_PATH . '/admin/products_import.php';
        break;
    case 'payments':
        include BASE_PATH . '/admin/payments.php';
        break;
    default:
        render_error(404);
        break;
}
