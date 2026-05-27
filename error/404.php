<?php
$pageTitle = '404 - Tidak Ditemukan';
include __DIR__ . '/../views/partials/header.php';
?>
<section class="section" data-aos="fade-up">
    <div class="hero-card">
        <h2>404 - Halaman Tidak Ditemukan</h2>
        <p>Halaman yang kamu cari tidak tersedia. Silakan kembali ke beranda.</p>
        <a class="btn" href="<?= base_url() ?>" data-transition>Kembali ke Beranda</a>
    </div>
</section>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
