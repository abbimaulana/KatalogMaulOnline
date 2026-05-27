<?php
$active = 'products';
$pageTitle = 'Manajemen Produk';

if (is_post() && request_value('action') === 'delete') {
    if (!verify_csrf()) {
        set_flash('error', 'Token tidak valid.');
    } else {
        $productId = sanitize_text((string) request_value('product_id'));
        if ($productId) {
            delete_product($productId);
            set_flash('success', 'Produk berhasil dihapus.');
        }
    }
    redirect('admin/products');
}

$products = get_all_products();

include __DIR__ . '/partials/header.php';
?>
<section class="section">
    <div class="admin-actions">
        <a class="btn" href="<?= base_url('admin/products/create') ?>" data-transition>Tambah Produk</a>
        <a class="btn btn-outline" href="<?= base_url('admin/products/export') ?>">Export CSV</a>
        <a class="btn btn-outline" href="<?= base_url('admin/products/import') ?>" data-transition>Import CSV</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Kode</th>
                <th>Harga</th>
                <th>Bayar Langsung</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="6">Belum ada produk.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= e($product['name']) ?></td>
                        <td><?= e($product['product_code']) ?></td>
                        <td><?= format_currency((int) $product['price']) ?></td>
                        <td><?= (int) $product['is_direct_payment'] === 1 ? 'Ya' : 'Tidak' ?></td>
                        <td><?= (int) $product['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?></td>
                        <td>
                            <a class="btn btn-outline" href="<?= base_url('admin/products/edit') ?>?id=<?= e($product['public_id']) ?>" data-transition>Edit</a>
                            <form method="post" action="<?= base_url('admin/products') ?>" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= e($product['public_id']) ?>">
                                <button class="btn" type="submit" onclick="return confirm('Hapus produk ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
