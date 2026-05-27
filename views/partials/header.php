<?php
$pageTitle = $pageTitle ?? config('app.name');
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= e(config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body class="page">
<div id="loader">
    <div class="loader-spin"></div>
</div>
<header class="navbar">
    <div class="container nav-inner">
        <a href="<?= base_url() ?>" class="brand" data-transition>
            <span class="brand-logo">M</span>
            <span>Maul Online Shop</span>
        </a>
        <nav class="nav-links">
            <a href="<?= base_url() ?>" class="<?= $active === 'home' ? 'active' : '' ?>" data-transition>Beranda</a>
            <a href="<?= base_url('catalog') ?>" class="<?= $active === 'catalog' ? 'active' : '' ?>" data-transition>Katalog</a>
            <a href="<?= base_url('about') ?>" class="<?= $active === 'about' ? 'active' : '' ?>" data-transition>Tentang</a>
            <a href="<?= base_url('contact') ?>" class="<?= $active === 'contact' ? 'active' : '' ?>" data-transition>Kontak</a>
            <a href="<?= base_url('admin/login') ?>" class="btn btn-outline" data-transition>Admin</a>
        </nav>
    </div>
</header>
<?php if ($flash = get_flash()): ?>
    <div class="toast <?= e($flash['type']) ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>
<main class="container">
