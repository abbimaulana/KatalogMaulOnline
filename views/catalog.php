<?php
$active = 'catalog';
include __DIR__ . '/partials/header.php';
?>
<section class="section">
    <div class="section-title">
        <h2>Katalog Produk</h2>
        <span class="chip">Update real-time</span>
    </div>
    <div class="grid">
        <?php if (empty($products)): ?>
            <div class="card" style="padding:24px;">Produk belum tersedia. Admin dapat menambahkan melalui dashboard.</div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="card" data-aos="fade-up">
                    <img src="<?= e($product['image_path'] ? upload_url($product['image_path']) : asset_url('images/logo.svg')) ?>" alt="<?= e($product['name']) ?>">
                    <div class="card-body">
                        <h3><?= e($product['name']) ?></h3>
                        <p><?= e($product['description']) ?></p>
                        <div class="price"><?= format_currency((int) $product['price']) ?></div>
                        <div style="margin-top:12px; display:flex; gap:10px; align-items:center;">
                            <span class="chip"><?= e($product['product_code']) ?></span>
                            <?php if ((int) $product['is_direct_payment'] === 1): ?>
                                <span class="chip">Bayar Langsung</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="section" data-aos="fade-up">
    <div class="section-title">
        <h2>Checkout Sekarang</h2>
        <span class="chip">Form cepat & aman</span>
    </div>
    <form class="form" method="post" action="<?= base_url('checkout') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="product_id">Pilih Produk</label>
            <select id="product_id" name="product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= e($product['public_id']) ?>"><?= e($product['name']) ?> (<?= e($product['product_code']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="buyer_name">Nama Pembeli</label>
            <input id="buyer_name" name="buyer_name" type="text" placeholder="Nama lengkap" required>
        </div>
        <div class="form-group">
            <label for="buyer_phone">No. WhatsApp</label>
            <input id="buyer_phone" name="buyer_phone" type="tel" placeholder="08xxxxxxxxxx" required>
        </div>
        <div class="form-group">
            <label for="buyer_address">Alamat Pengiriman</label>
            <textarea id="buyer_address" name="buyer_address" placeholder="Alamat lengkap"></textarea>
        </div>
        <div class="form-group">
            <label for="quantity">Jumlah</label>
            <input id="quantity" name="quantity" type="number" min="1" value="1" required>
        </div>
        <button class="btn" type="submit">Lanjut Checkout</button>
    </form>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
