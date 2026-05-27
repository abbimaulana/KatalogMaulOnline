# Maul Online Shop (Serverless PPOB Catalog)

Website katalog e-commerce produk digital/PPOB dengan tema **Blue Dark**, animasi smooth, dan arsitektur serverless berbasis **Cloudflare Pages + Cloudflare Workers + Google Apps Script + Google Sheets**.

## Struktur Direktori
```
/ (root)
├─ assets/
│  ├─ css/style.css
│  ├─ js/app.js
│  ├─ js/admin.js
│  └─ images/placeholder.svg
├─ views/
│  ├─ index.html
│  ├─ catalog.html
│  ├─ about.html
│  └─ contact.html
├─ checkout/
│  ├─ payment.html
│  └─ confirm.html
├─ admin/
│  ├─ index.html
│  └─ kelola.html
├─ error/
│  ├─ 400.html
│  ├─ 401.html
│  ├─ 403.html
│  ├─ 404.html
│  └─ 503.html
├─ worker.js
└─ Code.gs
```

## Masterclass Implementasi & Deployment
### 1) Push ke GitHub
1. Pastikan repo sudah berisi file di atas.
2. Commit perubahan.
3. Push ke GitHub (branch utama).

### 2) Setup Google Sheets (Database)
1. Buat spreadsheet baru dengan nama **Maul Online Shop DB**.
2. Tidak perlu membuat sheet manual; `Code.gs` akan membuat sheet otomatis:
   - `Products`
   - `Orders`
   - `Config`

### 3) Deploy Google Apps Script (Backend API)
1. Buka spreadsheet → **Extensions → Apps Script**.
2. Salin seluruh isi `Code.gs` ke editor Apps Script.
3. **Project Settings → Script Properties**: tambahkan `DRIVE_FOLDER_ID` (folder Google Drive untuk gambar).
4. **Deploy → New Deployment → Web App**:
   - Execute as: **Me**
   - Who has access: **Anyone**
5. Copy URL Web App → simpan sebagai `GAS_URL` di Worker.

### 4) Deploy Cloudflare Worker (Proxy API + Webhook)
1. Buat Worker baru di Cloudflare.
2. Copy `worker.js` ke editor Worker.
3. Set Environment Variables (Secrets):
   - `GAS_URL` (URL Web App dari Apps Script)
   - `WA_ACCESS_TOKEN`
   - `WA_PHONE_ID`
   - `WA_ADMIN_NUMBER` (contoh: 6287864865721)
   - `WA_CS_NUMBER` (contoh: 6287872369848)
   - `TELEGRAM_TOKEN`
   - `TELEGRAM_CHAT_ID`
   - `DISCORD_WEBHOOK_URL`
4. Deploy Worker dan buat route: `https://domainmu.com/api/*`.

### 5) Deploy Frontend ke Cloudflare Pages
1. Hubungkan repo GitHub ke **Cloudflare Pages**.
2. Build command: **(kosong)**
3. Output directory: **/**
4. Pastikan Worker sudah aktif agar `/api/*` bisa diakses.

> Jika Worker menggunakan domain berbeda, tambahkan di semua HTML:
> ```html
> <script>window.MAUL_API_BASE = 'https://worker-domain.com/api';</script>
> ```

### 6) Proteksi Admin dengan Cloudflare Access
1. Buka **Zero Trust → Access → Applications**.
2. Tambahkan aplikasi **Self-hosted** untuk domain.
3. Set **Include path**: `/admin/*`.
4. Set policy agar hanya email owner yang diizinkan.

### 7) Konfigurasi Jam Operasional & Pembayaran
1. Masuk `/admin/index.html` (melalui Access).
2. Atur:
   - Mode Auto / Manual
   - Jam Buka / Jam Tutup
   - Payment QRIS, BSI, DANA, OVO
3. Simpan perubahan (data akan tersimpan di Sheet `Config`).

## Flow Checkout PPOB
1. Pembeli memilih produk di **Katalog**.
2. Sistem membuat order dan mengarahkan:
   - **Bayar Langsung** → `/checkout/payment.html`
   - **Nonaktif** → `/checkout/confirm.html`
3. Saat konfirmasi, Worker:
   - Menyimpan status pesanan di Sheets
   - Mengirim notifikasi ke WhatsApp, Telegram, dan Discord
   - Redirect ke WA Bot CS dengan template pesan

## Catatan
- Semua halaman memuat footer hak cipta: **© 2026 Maul Online Shop. Proudly powered by GitHub, Cloudflare & Google.**
- Jam operasional otomatis dihitung dengan timezone **Asia/Jakarta (WIB)**.
- Error pages tersedia di `/error/` dengan tema Blue Dark.
