<?php
$active = '';
include __DIR__ . '/partials/header.php';
?>
<section class="section" data-aos="fade-up">
    <div class="hero-card">
        <h2>Checkout Pembayaran</h2>
        <p>Silakan lakukan pembayaran sesuai instruksi di bawah ini. Setelah selesai, klik tombol konfirmasi.</p>
    </div>
</section>
<section class="section" data-aos="fade-up">
    <div class="grid">
        <div class="form">
            <h3>Detail Pesanan</h3>
            <p><strong>Kode Pesanan:</strong> <?= e($order['order_code']) ?></p>
            <p><strong>Produk:</strong> <?= e($order['product_name']) ?> (<?= e($order['product_code']) ?>)</p>
            <p><strong>Jumlah:</strong> <?= e((string) $order['quantity']) ?></p>
            <p><strong>Total:</strong> <?= format_currency((int) $order['total_price']) ?></p>
            <p><strong>Nama Pembeli:</strong> <?= e($order['buyer_name']) ?></p>
            <p><strong>No. HP:</strong> <?= e($order['buyer_phone']) ?></p>
        </div>
        <div class="form">
            <h3>Informasi Pembayaran</h3>
            <p><strong>Bank:</strong> <?= e($payment['bank_name'] ?? '') ?></p>
            <p><strong>Nama Rekening:</strong> <?= e($payment['account_name'] ?? '') ?></p>
            <p><strong>No. Rekening:</strong> <?= e($payment['account_number'] ?? '') ?></p>
            <?php if (!empty($payment['qris_image'])): ?>
                <div style="margin-top:16px;">
                    <img src="<?= e(upload_url($payment['qris_image'])) ?>" alt="QRIS" style="border-radius:16px; border:1px solid var(--border);">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<section class="section" data-aos="fade-up">
    <form method="post" action="<?= base_url('confirm') ?>" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="order_id" value="<?= e($order['public_id']) ?>">
        <button class="btn" type="submit">Konfirmasi Pembayaran</button>
    </form>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
