<?php
$active = 'contact';
include __DIR__ . '/partials/header.php';
?>
<section class="section" data-aos="fade-up">
    <div class="hero-card">
        <h2>Kontak Kami</h2>
        <p>Butuh bantuan? Tim kami siap membantu 24/7 melalui kanal berikut.</p>
        <div style="display:grid; gap:12px; margin-top:20px;">
            <div class="card" style="padding:16px;">
                <strong>WhatsApp CS</strong><br>
                <span style="color:var(--muted);">+62 878 7236 9848</span>
            </div>
            <div class="card" style="padding:16px;">
                <strong>Email</strong><br>
                <span style="color:var(--muted);">support@maulshop.local</span>
            </div>
            <div class="card" style="padding:16px;">
                <strong>Instagram</strong><br>
                <span style="color:var(--muted);">@maulshop</span>
            </div>
        </div>
    </div>
</section>
<section class="section" data-aos="fade-up">
    <form class="form" method="post" action="mailto:support@maulshop.local">
        <div class="form-group">
            <label>Nama</label>
            <input type="text" placeholder="Nama lengkap">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" placeholder="email@domain.com">
        </div>
        <div class="form-group">
            <label>Pesan</label>
            <textarea placeholder="Tulis pesan"></textarea>
        </div>
        <button class="btn" type="submit">Kirim Pesan</button>
    </form>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
