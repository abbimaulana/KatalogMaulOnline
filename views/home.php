<?php
$active = 'home';
include __DIR__ . '/partials/header.php';
?>
<section class="hero" data-aos="fade-up">
    <div>
        <span class="badge">Catalog Premium • WhatsApp Style</span>
        <h1>Belanja produk unggulan dengan pengalaman katalog yang halus & elegan.</h1>
        <p>Maul Online Shop menghadirkan katalog profesional dengan alur checkout fleksibel, pembayaran cepat, dan notifikasi otomatis ke admin.</p>
        <div style="display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;">
            <a class="btn" href="<?= base_url('catalog') ?>" data-transition>Lihat Katalog</a>
            <a class="btn btn-outline" href="<?= base_url('contact') ?>" data-transition>Hubungi CS</a>
        </div>
    </div>
    <div class="hero-card" data-aos="zoom-in">
        <h3>Kenapa Maul Online Shop?</h3>
        <ul style="color: var(--muted); line-height: 1.8; padding-left: 18px;">
            <li>Dark theme premium mirip WhatsApp Business.</li>
            <li>Animasi halus untuk pengalaman modern.</li>
            <li>Checkout otomatis ke Telegram, Discord, & WhatsApp.</li>
            <li>Admin dashboard untuk update produk cepat.</li>
        </ul>
    </div>
</section>

<section class="section">
    <div class="section-title">
        <h2>Produk Terbaru</h2>
        <a href="<?= base_url('catalog') ?>" class="btn btn-outline" data-transition>Lihat Semua</a>
    </div>
    <div class="grid">
        <?php if (empty($products)): ?>
            <div class="card" style="padding:24px;">Belum ada produk. Tambahkan dari dashboard admin.</div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="card" data-aos="fade-up">
                    <img src="<?= e($product['image_path'] ? upload_url($product['image_path']) : asset_url('images/logo.svg')) ?>" alt="<?= e($product['name']) ?>">
                    <div class="card-body">
                        <h3><?= e($product['name']) ?></h3>
                        <p><?= e($product['description']) ?></p>
                        <div class="price"><?= format_currency((int) $product['price']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="section" data-aos="fade-up">
    <div class="hero-card">
        <h2>Transisi Checkout Super Fleksibel</h2>
        <p>Jika produk mengaktifkan Bayar Langsung, pembeli langsung diarahkan ke halaman pembayaran dengan QRIS atau rekening. Jika tidak, pembeli cukup konfirmasi pesanan dan data langsung terkirim ke admin.</p>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
