<?php
$active = 'products';
$pageTitle = 'Form Produk';
$productId = sanitize_text((string) request_value('id'));
$editing = $productId !== '';
$product = $editing ? get_product_by_public_id($productId) : null;

if ($editing && !$product) {
    render_error(404);
    return;
}

if (is_post()) {
    if (!verify_csrf()) {
        set_flash('error', 'Token tidak valid.');
        redirect('admin/products');
    }

    $name = sanitize_text((string) request_value('name'));
    $description = sanitize_text((string) request_value('description'));
    $price = (int) request_value('price');
    $directPayment = request_value('is_direct_payment') === '1' ? 1 : 0;
    $isActive = request_value('is_active') === '1' ? 1 : 0;

    if ($name === '' || $price <= 0) {
        set_flash('error', 'Nama dan harga wajib diisi.');
        redirect($editing ? 'admin/products/edit?id=' . $productId : 'admin/products/create');
    }

    $imagePath = handle_image_upload($_FILES['image'] ?? [], $product['image_path'] ?? null);

    $data = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'image_path' => $imagePath,
        'is_direct_payment' => $directPayment,
        'is_active' => $isActive,
    ];

    if ($editing) {
        update_product($productId, $data);
        set_flash('success', 'Produk berhasil diperbarui.');
    } else {
        create_product($data);
        set_flash('success', 'Produk berhasil ditambahkan.');
    }

    redirect('admin/products');
}

include __DIR__ . '/partials/header.php';
?>
<section class="section">
    <form class="form" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="name" value="<?= e($product['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description"><?= e($product['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Harga</label>
            <input type="number" name="price" min="0" value="<?= e((string) ($product['price'] ?? '')) ?>" required>
        </div>
        <div class="form-group">
            <label>Gambar Produk (jpg/png/webp, max 2MB)</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="form-group">
            <label>Bayar Langsung</label>
            <select name="is_direct_payment">
                <option value="0" <?= isset($product['is_direct_payment']) && (int) $product['is_direct_payment'] === 0 ? 'selected' : '' ?>>Tidak</option>
                <option value="1" <?= isset($product['is_direct_payment']) && (int) $product['is_direct_payment'] === 1 ? 'selected' : '' ?>>Ya</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status Produk</label>
            <select name="is_active">
                <option value="1" <?= !isset($product['is_active']) || (int) $product['is_active'] === 1 ? 'selected' : '' ?>>Aktif</option>
                <option value="0" <?= isset($product['is_active']) && (int) $product['is_active'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        <button class="btn" type="submit"><?= $editing ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
        <a class="btn btn-outline" href="<?= base_url('admin/products') ?>" data-transition>Kembali</a>
    </form>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
