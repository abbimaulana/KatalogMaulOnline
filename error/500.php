<?php
$pageTitle = '500 - Server Error';
include __DIR__ . '/../views/partials/header.php';
?>
<section class="section" data-aos="fade-up">
    <div class="hero-card">
        <h2>500 - Terjadi Kesalahan</h2>
        <p>Server mengalami kendala. Silakan coba lagi nanti.</p>
        <a class="btn" href="<?= base_url() ?>" data-transition>Kembali ke Beranda</a>
    </div>
</section>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
