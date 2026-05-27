<?php
$active = '';
include __DIR__ . '/partials/header.php';
?>
<section class="section" data-aos="fade-up">
    <div class="hero-card">
        <h2>Konfirmasi Pesanan</h2>
        <p>Pesanan kamu siap dikonfirmasi. Klik tombol di bawah untuk mengirim notifikasi ke admin.</p>
    </div>
</section>
<section class="section" data-aos="fade-up">
    <div class="form">
        <h3>Detail Pesanan</h3>
        <p><strong>Kode Pesanan:</strong> <?= e($order['order_code']) ?></p>
        <p><strong>Produk:</strong> <?= e($order['product_name']) ?> (<?= e($order['product_code']) ?>)</p>
        <p><strong>Jumlah:</strong> <?= e((string) $order['quantity']) ?></p>
        <p><strong>Total:</strong> <?= format_currency((int) $order['total_price']) ?></p>
        <p><strong>Nama Pembeli:</strong> <?= e($order['buyer_name']) ?></p>
        <p><strong>No. HP:</strong> <?= e($order['buyer_phone']) ?></p>
        <p><strong>Alamat:</strong> <?= e($order['buyer_address']) ?></p>
    </div>
</section>
<section class="section" data-aos="fade-up">
    <form method="post" action="<?= base_url('confirm') ?>" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="order_id" value="<?= e($order['public_id']) ?>">
        <button class="btn" type="submit">Konfirmasi Pesanan</button>
    </form>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
