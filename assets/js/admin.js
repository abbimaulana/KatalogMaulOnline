const API_BASE = window.MAUL_API_BASE || '/api';

const adminState = {
  products: [],
  editingId: null,
  imageUrl: '',
};

const qs = (selector, scope = document) => scope.querySelector(selector);
const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

const fetchJSON = async (url, options = {}) => {
  const response = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...options,
  });
  if (!response.ok) {
    const text = await response.text();
    throw new Error(text || 'Request failed');
  }
  return response.json();
};

const loadConfig = async () => {
  try {
    const config = await fetchJSON(`${API_BASE}/config`);
    qs('#store-mode').value = config.store_mode || 'auto';
    qs('#store-status').value = config.store_status || 'open';
    qs('#store-open').value = config.open_time || '08:00';
    qs('#store-close').value = config.close_time || '22:00';
    qs('#payment-bsi').value = config.bank_bsi || '';
    qs('#payment-dana').value = config.ewallet_dana || '';
    qs('#payment-ovo').value = config.ewallet_ovo || '';
    adminState.imageUrl = config.qris_url || '';
    if (adminState.imageUrl) {
      qs('#qris-preview').src = adminState.imageUrl;
    }
  } catch (error) {
    console.error(error);
  }
};

const saveConfig = async (event) => {
  event.preventDefault();
  const payload = {
    store_mode: qs('#store-mode').value,
    store_status: qs('#store-status').value,
    open_time: qs('#store-open').value,
    close_time: qs('#store-close').value,
    bank_bsi: qs('#payment-bsi').value,
    ewallet_dana: qs('#payment-dana').value,
    ewallet_ovo: qs('#payment-ovo').value,
    qris_url: adminState.imageUrl,
  };

  try {
    qs('#config-submit').disabled = true;
    await fetchJSON(`${API_BASE}/admin/config`, {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    alert('Pengaturan berhasil disimpan.');
  } catch (error) {
    alert('Gagal menyimpan konfigurasi.');
    console.error(error);
  } finally {
    qs('#config-submit').disabled = false;
  }
};

const uploadQRIS = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = async () => {
    try {
      const base64 = reader.result.split(',')[1];
      const response = await fetchJSON(`${API_BASE}/admin/upload`, {
        method: 'POST',
        body: JSON.stringify({
          filename: file.name,
          content_type: file.type,
          data: base64,
        }),
      });
      adminState.imageUrl = response.url;
      qs('#qris-preview').src = adminState.imageUrl;
    } catch (error) {
      alert('Upload QRIS gagal.');
      console.error(error);
    }
  };
  reader.readAsDataURL(file);
};

const loadProducts = async () => {
  try {
    const data = await fetchJSON(`${API_BASE}/products?mode=admin`);
    adminState.products = data.items || [];
    renderProducts();
  } catch (error) {
    console.error(error);
  }
};

const renderProducts = () => {
  const tableBody = qs('#products-body');
  if (!tableBody) return;
  tableBody.innerHTML = '';
  adminState.products.forEach((product) => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${product.code}</td>
      <td>${product.name}</td>
      <td>${product.price}</td>
      <td>${product.is_direct_payment ? 'Ya' : 'Tidak'}</td>
      <td>${product.is_active ? 'Aktif' : 'Nonaktif'}</td>
      <td>
        <button class="btn btn-ghost" data-edit="${product.id}">Edit</button>
        <button class="btn btn-secondary" data-delete="${product.id}">Hapus</button>
      </td>
    `;
    tableBody.appendChild(row);
  });

  qsa('[data-edit]').forEach((button) => button.addEventListener('click', () => openProductForm(button.dataset.edit)));
  qsa('[data-delete]').forEach((button) => button.addEventListener('click', () => deleteProduct(button.dataset.delete)));
};

const openProductForm = (productId = null) => {
  const modal = qs('#product-modal');
  if (!modal) return;
  adminState.editingId = productId;
  const product = adminState.products.find((item) => item.id === productId);
  qs('#product-name').value = product?.name || '';
  qs('#product-desc').value = product?.description || '';
  qs('#product-price').value = product?.price || '';
  qs('#product-direct').value = product?.is_direct_payment ? 'yes' : 'no';
  qs('#product-active').value = product?.is_active ? 'yes' : 'no';
  qs('#product-image').value = '';
  adminState.imageUrl = product?.image_url || '';
  qs('#product-preview').src = adminState.imageUrl || '/assets/images/placeholder.png';
  modal.classList.add('show');
};

const closeProductForm = () => {
  const modal = qs('#product-modal');
  if (modal) modal.classList.remove('show');
};

const handleProductImage = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = async () => {
    try {
      const base64 = reader.result.split(',')[1];
      const response = await fetchJSON(`${API_BASE}/admin/upload`, {
        method: 'POST',
        body: JSON.stringify({
          filename: file.name,
          content_type: file.type,
          data: base64,
        }),
      });
      adminState.imageUrl = response.url;
      qs('#product-preview').src = adminState.imageUrl;
    } catch (error) {
      alert('Upload gambar gagal.');
    }
  };
  reader.readAsDataURL(file);
};

const saveProduct = async (event) => {
  event.preventDefault();
  const payload = {
    name: qs('#product-name').value.trim(),
    description: qs('#product-desc').value.trim(),
    price: Number(qs('#product-price').value),
    image_url: adminState.imageUrl,
    is_direct_payment: qs('#product-direct').value === 'yes',
    is_active: qs('#product-active').value === 'yes',
  };

  if (!payload.name || !payload.price) {
    alert('Nama & harga wajib diisi.');
    return;
  }

  try {
    const method = adminState.editingId ? 'PUT' : 'POST';
    const url = adminState.editingId ? `${API_BASE}/products/${adminState.editingId}` : `${API_BASE}/products`;
    await fetchJSON(url, {
      method,
      body: JSON.stringify(payload),
    });
    await loadProducts();
    closeProductForm();
  } catch (error) {
    alert('Gagal menyimpan produk.');
    console.error(error);
  }
};

const deleteProduct = async (productId) => {
  if (!confirm('Hapus produk ini?')) return;
  try {
    await fetchJSON(`${API_BASE}/products/${productId}`, { method: 'DELETE' });
    await loadProducts();
  } catch (error) {
    alert('Gagal menghapus produk.');
  }
};

const initAdminDashboard = async () => {
  await loadConfig();
  qs('#config-form').addEventListener('submit', saveConfig);
  qs('#qris-upload').addEventListener('change', uploadQRIS);
};

const initAdminProducts = async () => {
  await loadProducts();
  qs('#add-product').addEventListener('click', () => openProductForm());
  qs('[data-modal-close]').addEventListener('click', closeProductForm);
  qs('#product-form').addEventListener('submit', saveProduct);
  qs('#product-image').addEventListener('change', handleProductImage);
  const modal = qs('#product-modal');
  modal.addEventListener('click', (event) => {
    if (event.target === modal) closeProductForm();
  });
};

const initAdmin = async () => {
  const page = document.body.dataset.page;
  if (page === 'admin-dashboard') {
    await initAdminDashboard();
  }
  if (page === 'admin-products') {
    await initAdminProducts();
  }
};

document.addEventListener('DOMContentLoaded', initAdmin);
