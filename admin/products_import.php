<?php
$active = 'products';
$pageTitle = 'Import Produk';

if (is_post()) {
    if (!verify_csrf()) {
        set_flash('error', 'Token tidak valid.');
        redirect('admin/products/import');
    }

    $file = $_FILES['csv'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'File CSV tidak ditemukan.');
        redirect('admin/products/import');
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        set_flash('error', 'Gagal membaca file CSV.');
        redirect('admin/products/import');
    }

    $header = fgetcsv($handle);
    if (!$header) {
        set_flash('error', 'Header CSV kosong.');
        redirect('admin/products/import');
    }

    $header = array_map('trim', $header);
    $count = 0;
    $skipped = 0;
    $skippedRows = [];
    $rowIndex = 1;

    while (($row = fgetcsv($handle)) !== false) {
        $rowIndex++;
        $data = array_combine($header, $row);
        if (!$data || empty($data['name'])) {
            $skipped++;
            $skippedRows[] = $rowIndex;
            continue;
        }

        $productCode = $data['product_code'] ?? null;
        $existing = $productCode ? get_product_by_code($productCode) : null;

        $payload = [
            'name' => sanitize_text((string) ($data['name'] ?? '')),
            'description' => sanitize_text((string) ($data['description'] ?? '')),
            'price' => (int) ($data['price'] ?? 0),
            'image_path' => $existing['image_path'] ?? null,
            'is_direct_payment' => (int) ($data['is_direct_payment'] ?? 0),
            'is_active' => (int) ($data['is_active'] ?? 1),
        ];

        if ($payload['name'] === '' || $payload['price'] <= 0) {
            $skipped++;
            $skippedRows[] = $rowIndex;
            continue;
        }

        if ($existing) {
            update_product($existing['public_id'], $payload);
        } else {
            if ($productCode) {
                $payload['product_code'] = $productCode;
            }
            create_product($payload);
        }
        $count++;
    }

    fclose($handle);

    $message = 'Import selesai. Berhasil: ' . $count . ' baris, dilewati: ' . $skipped . ' baris.';
    if ($skipped > 0) {
        $preview = array_slice($skippedRows, 0, 5);
        $message .= ' Baris terlewat: ' . implode(', ', $preview) . (count($skippedRows) > 5 ? '...' : '') . '.';
    }
    set_flash('success', $message);
    redirect('admin/products');
}

include __DIR__ . '/partials/header.php';
?>
<section class="section">
    <div class="hero-card">
        <h2>Import CSV</h2>
        <p>Gunakan format kolom: product_code, name, description, price, is_direct_payment, is_active. File CSV dapat dibuka di Excel.</p>
    </div>
</section>
<section class="section">
    <form class="form" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Upload CSV</label>
            <input type="file" name="csv" accept=".csv" required>
        </div>
        <button class="btn" type="submit">Import Produk</button>
        <a class="btn btn-outline" href="<?= base_url('admin/products') ?>" data-transition>Kembali</a>
    </form>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
