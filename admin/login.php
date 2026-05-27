<?php
if (admin_logged_in()) {
    redirect('admin');
}

if (is_post()) {
    if (!verify_csrf()) {
        set_flash('error', 'Token login tidak valid.');
    } else {
        $username = sanitize_text((string) request_value('username'));
        $password = (string) request_value('password');
        if (login_admin($username, $password)) {
            set_flash('success', 'Selamat datang, ' . $username . '!');
            redirect('admin');
        }
        set_flash('error', 'Username atau password salah.');
    }
}

$pageTitle = 'Login Admin';
include __DIR__ . '/partials/header.php';
?>
<section class="section">
    <form class="form" method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button class="btn" type="submit">Masuk Dashboard</button>
    </form>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
