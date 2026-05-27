<?php
$active = 'dashboard';
$pageTitle = 'Dashboard';
$productsCount = count_products();
$ordersCount = count_orders();
$orders = list_orders(5);
$securityWarnings = [];

if (config('security.csrf_key') === '') {
    $securityWarnings[] = 'Isi security.csrf_key di core/config.php dengan string acak.';
}

$stmt = db()->prepare('SELECT COUNT(*) FROM admins');
$stmt->execute();
$adminCount = (int) $stmt->fetchColumn();
if ($adminCount === 0) {
    $securityWarnings[] = 'Belum ada akun admin. Tambahkan akun admin di tabel admins.';
}
include __DIR__ . '/partials/header.php';
?>
<section class="section">
    <?php if (!empty($securityWarnings)): ?>
        <div class="hero-card" style="border-color: rgba(255, 204, 102, 0.5);">
            <h3>Security Checklist</h3>
            <ul style="color: var(--muted); line-height: 1.8; padding-left: 18px;">
                <?php foreach ($securityWarnings as $warning): ?>
                    <li><?= e($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="grid">
        <div class="card">
            <div class="card-body">
                <h3>Total Produk</h3>
                <div class="price" style="font-size:2rem;"><?= e((string) $productsCount) ?></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Total Pesanan</h3>
                <div class="price" style="font-size:2rem;"><?= e((string) $ordersCount) ?></div>
            </div>
        </div>
    </div>
</section>
<section class="section">
    <div class="section-title">
        <h2>Pesanan Terbaru</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Produk</th>
                <th>Pembeli</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5">Belum ada pesanan masuk.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= e($order['order_code']) ?></td>
                        <td><?= e($order['product_name']) ?></td>
                        <td><?= e($order['buyer_name']) ?></td>
                        <td><?= e($order['status']) ?></td>
                        <td><?= format_currency((int) $order['total_price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
