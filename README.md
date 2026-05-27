# Maul Online Shop

Website katalog/e-commerce premium dengan dark theme ala WhatsApp Business Catalog, checkout fleksibel, serta notifikasi otomatis ke Telegram, Discord, dan WhatsApp.

## Struktur Direktori
```
/ (root)
├─ assets/
│  ├─ css/style.css
│  ├─ js/app.js
│  └─ images/logo.svg
├─ uploads/
│  └─ .htaccess
├─ core/
│  ├─ admin_router.php
│  ├─ auth.php
│  ├─ config.php
│  ├─ db.php
│  ├─ helpers.php
│  ├─ init.php
│  ├─ security.php
│  ├─ store.php
│  ├─ upload.php
│  └─ webhooks.php
├─ views/
│  ├─ partials/header.php
│  ├─ partials/footer.php
│  ├─ home.php
│  ├─ catalog.php
│  ├─ about.php
│  ├─ contact.php
│  ├─ checkout_payment.php
│  └─ checkout_confirm.php
├─ admin/
│  ├─ partials/header.php
│  ├─ partials/footer.php
│  ├─ login.php
│  ├─ dashboard.php
│  ├─ products.php
│  ├─ product_form.php
│  ├─ products_export.php
│  ├─ products_import.php
│  └─ payments.php
├─ error/
│  ├─ 404.php
│  ├─ 403.php
│  └─ 500.php
├─ database.sql
├─ maintenance.php
├─ index.php
└─ .htaccess
```

## Setup Lokal & Deploy ke InfinityFree
1. **Upload file** ke folder `htdocs` InfinityFree (atau `public_html`).
2. **Buat database** di cPanel InfinityFree:
   - Masuk ke **MySQL Databases** → buat DB + user.
3. **Import database**:
   - Buka **phpMyAdmin** → pilih DB → Import → gunakan `database.sql`.
4. **Konfigurasi aplikasi**:
   - Edit `core/config.php`:
     - `db.host`, `db.name`, `db.user`, `db.pass`
     - `bots.telegram_token`, `bots.telegram_chat_id`, `bots.discord_webhook_url`
     - `payment.*` (rekening & QRIS)
     - `security.csrf_key` (wajib ganti)
   - Opsional: set `app.base_url` jika ingin fixed URL.
5. **Setel admin**:
   - Login default: `admin` / `admin123` (ubah segera).
   - Ubah password dengan mengganti hash di tabel `admins`.
     - Gunakan PHP: `password_hash('password_baru', PASSWORD_DEFAULT)`.
6. **Cloudflare (Strict SSL)**:
   - Aktifkan **Full (Strict)**.
   - Pastikan DNS mengarah ke InfinityFree.
   - `.htaccess` sudah force HTTPS, cocok untuk Cloudflare.

## Routing & Clean URL
- Semua URL tanpa `.php`.
- Contoh: `/catalog`, `/checkout_payment`, `/admin/products`.
- Front controller di `index.php` menangani routing publik dan admin.

## Keamanan
- PDO + Prepared Statements (anti SQLi)
- `htmlspecialchars()` untuk output (anti XSS)
- CSRF token di semua form
- UUID/Slug acak untuk mencegah IDOR
- `.htaccess` memblokir akses `core/` dan file sensitif (.env/.ini)
- `/uploads/.htaccess` mencegah eksekusi script

## Integrasi Bot
### Telegram
1. Buat bot via **@BotFather**.
2. Ambil token.
3. Dapatkan `chat_id` (gunakan bot @get_id atau API getUpdates).
4. Isi di `core/config.php`.

### Discord
1. Buat channel webhook di server Discord.
2. Copy Webhook URL.
3. Isi di `core/config.php`.

## Alur Checkout
1. Pembeli isi form di `/catalog`.
2. Sistem membuat order & mengecek `is_direct_payment`:
   - **Aktif** → `/checkout_payment`
   - **Nonaktif** → `/checkout_confirm`
3. Setelah klik konfirmasi, sistem:
   - Kirim notifikasi ke Telegram & Discord.
   - Redirect ke WA bot CS `6287872369848` dengan template pesan.

## Maintenance Mode
- Aktifkan dengan mengubah `app.maintenance` di `core/config.php` menjadi `true`.
- Halaman yang muncul: `maintenance.php`.

## Catatan Upload Gambar
- Format: `.jpg`, `.png`, `.webp`
- Max size: 2MB
- Nama file diacak otomatis

## Import / Export CSV
- Export: klik **Export CSV** di admin produk.
- Import: gunakan format kolom:
  `product_code, name, description, price, is_direct_payment, is_active`
- CSV kompatibel dibuka di Excel.
