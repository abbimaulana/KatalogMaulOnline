<?php
$active = 'payments';
$pageTitle = 'Pengaturan Pembayaran';
$settings = get_payment_settings();

if (is_post()) {
    if (!verify_csrf()) {
        set_flash('error', 'Token tidak valid.');
        redirect('admin/payments');
    }

    $bankName = sanitize_text((string) request_value('bank_name'));
    $accountName = sanitize_text((string) request_value('account_name'));
    $accountNumber = sanitize_text((string) request_value('account_number'));

    $upload = handle_image_upload($_FILES['qris'] ?? [], $settings['qris_image'] ?? null);
    if ($upload['error']) {
        set_flash('error', $upload['error']);
        redirect('admin/payments');
    }

    save_payment_settings([
        'bank_name' => $bankName,
        'account_name' => $accountName,
        'account_number' => $accountNumber,
        'qris_image' => $upload['path'],
    ]);

    set_flash('success', 'Pengaturan pembayaran diperbarui.');
    redirect('admin/payments');
}

include __DIR__ . '/partials/header.php';
?>
<section class="section">
    <form class="form" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Nama Bank</label>
            <input type="text" name="bank_name" value="<?= e($settings['bank_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Nama Rekening</label>
            <input type="text" name="account_name" value="<?= e($settings['account_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>No. Rekening</label>
            <input type="text" name="account_number" value="<?= e($settings['account_number'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>QRIS (jpg/png/webp)</label>
            <input type="file" name="qris" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <button class="btn" type="submit">Simpan Pengaturan</button>
    </form>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
