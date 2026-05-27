<?php
$pageTitle = $pageTitle ?? 'Admin';
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body class="page">
<div id="loader">
    <div class="loader-spin"></div>
</div>
<header class="navbar">
    <div class="container nav-inner">
        <a href="<?= base_url('admin') ?>" class="brand" data-transition>
            <span class="brand-logo">A</span>
            <span>Admin Panel</span>
        </a>
        <nav class="nav-links">
            <a href="<?= base_url('admin') ?>" class="<?= $active === 'dashboard' ? 'active' : '' ?>" data-transition>Dashboard</a>
            <a href="<?= base_url('admin/products') ?>" class="<?= $active === 'products' ? 'active' : '' ?>" data-transition>Produk</a>
            <a href="<?= base_url('admin/payments') ?>" class="<?= $active === 'payments' ? 'active' : '' ?>" data-transition>Pembayaran</a>
            <a href="<?= base_url('admin/logout') ?>" class="btn btn-outline" data-transition>Logout</a>
        </nav>
    </div>
</header>
<?php if ($flash = get_flash()): ?>
    <div class="toast <?= e($flash['type']) ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>
<main class="container admin-wrapper">
