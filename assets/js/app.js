const API_BASE = window.MAUL_API_BASE || '/api';

const state = {
  storeStatus: null,
  selectedProduct: null,
  products: [],
};

const qs = (selector, scope = document) => scope.querySelector(selector);
const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(Number(value || 0));

const fetchJSON = async (url, options = {}) => {
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
    },
    ...options,
  });
  if (!response.ok) {
    const text = await response.text();
    throw new Error(text || 'Request failed');
  }
  return response.json();
};

const initLoader = () => {
  const loader = qs('#loader');
  if (!loader) return;
  window.addEventListener('load', () => loader.classList.add('hidden'));
};

const initTransitions = () => {
  qsa('a[data-transition]').forEach((link) => {
    link.addEventListener('click', (event) => {
      if (link.target === '_blank' || event.metaKey || event.ctrlKey) {
        return;
      }
      event.preventDefault();
      document.body.classList.add('fade-out');
      setTimeout(() => {
        window.location.href = link.href;
      }, 220);
    });
  });
};

const setActiveNav = () => {
  const path = window.location.pathname.replace(/\/$/, '');
  qsa('.nav-links a').forEach((link) => {
    if (link.getAttribute('href') === path || link.getAttribute('href') === `${path}.html`) {
      link.classList.add('active');
    }
  });
};

const applyStoreStatus = (status) => {
  const banner = qs('[data-store-banner]');
  const buttons = qsa('[data-buy-button], [data-checkout-button]');
  if (!status) return;

  if (!status.isOpen) {
    if (banner) {
      banner.textContent = status.message || 'Maaf, Toko Sedang Tutup. Silakan kembali pada jam operasional.';
      banner.classList.add('show');
    }
    buttons.forEach((button) => {
      button.disabled = true;
      button.setAttribute('aria-disabled', 'true');
    });
  } else if (banner) {
    banner.classList.remove('show');
  }

  const badge = qs('[data-store-status]');
  if (badge) {
    badge.textContent = status.isOpen ? 'Toko Buka' : 'Toko Tutup';
    badge.classList.toggle('closed', !status.isOpen);
  }
};

const loadStoreStatus = async () => {
  try {
    const data = await fetchJSON(`${API_BASE}/store-status`);
    state.storeStatus = data;
    applyStoreStatus(data);
  } catch (error) {
    console.warn('Gagal memuat status toko', error);
  }
};

const renderProducts = (products) => {
  const grid = qs('#catalog-grid');
  if (!grid) return;
  grid.innerHTML = '';
  if (!products.length) {
    grid.innerHTML = '<div class="card">Produk belum tersedia.</div>';
    return;
  }
  products.forEach((product) => {
    const card = document.createElement('article');
    card.className = 'card';
    card.innerHTML = `
      <img src="${product.image_url || '/assets/images/placeholder.svg'}" alt="${product.name}" loading="lazy" />
      <div class="tag">${product.is_direct_payment ? 'Bayar Langsung' : 'Konfirmasi'}</div>
      <h3>${product.name}</h3>
      <p>${product.description || 'Layanan digital cepat & aman.'}</p>
      <div class="card-footer">
        <span class="price">${formatCurrency(product.price)}</span>
        <button class="btn" data-buy-button data-product-id="${product.id}">Beli</button>
      </div>
    `;
    grid.appendChild(card);
  });

  qsa('[data-buy-button]', grid).forEach((button) => {
    button.addEventListener('click', () => openCheckoutModal(button.dataset.productId));
  });

  applyStoreStatus(state.storeStatus);
};

const openCheckoutModal = (productId) => {
  const modal = qs('#checkout-modal');
  const selected = state.products?.find((item) => item.id === productId);
  if (!modal || !selected) return;
  state.selectedProduct = selected;

  qs('[data-selected-product]').textContent = selected.name;
  qs('[data-selected-price]').textContent = formatCurrency(selected.price);
  modal.classList.add('show');
};

const closeCheckoutModal = () => {
  const modal = qs('#checkout-modal');
  if (modal) modal.classList.remove('show');
};

const submitCheckout = async (event) => {
  event.preventDefault();
  if (state.storeStatus && !state.storeStatus.isOpen) return;
  const form = event.target;
  const payload = {
    product_id: state.selectedProduct?.id,
    buyer_name: qs('#buyer-name', form).value.trim(),
    buyer_contact: qs('#buyer-contact', form).value.trim(),
    buyer_target: qs('#buyer-target', form).value.trim(),
    buyer_note: qs('#buyer-note', form).value.trim(),
  };

  if (!payload.product_id || !payload.buyer_name || !payload.buyer_contact || !payload.buyer_target) {
    alert('Lengkapi data pembeli & tujuan layanan terlebih dahulu.');
    return;
  }

  try {
    form.querySelector('button[type="submit"]').disabled = true;
    const response = await fetchJSON(`${API_BASE}/orders`, {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    const orderId = response.order_id || response.public_id;
    const target = response.is_direct_payment ? '/checkout/payment.html' : '/checkout/confirm.html';
    window.location.href = `${target}?order=${encodeURIComponent(orderId)}`;
  } catch (error) {
    alert('Gagal membuat pesanan. Coba beberapa saat lagi.');
    console.error(error);
  } finally {
    form.querySelector('button[type="submit"]').disabled = false;
  }
};

const initCatalog = async () => {
  try {
    const data = await fetchJSON(`${API_BASE}/products`);
    state.products = data.items || [];
    renderProducts(state.products);
  } catch (error) {
    console.error(error);
  }

  const modal = qs('#checkout-modal');
  if (modal) {
    qs('[data-modal-close]').addEventListener('click', closeCheckoutModal);
    modal.addEventListener('click', (event) => {
      if (event.target === modal) closeCheckoutModal();
    });
    qs('#checkout-form').addEventListener('submit', submitCheckout);
  }
};

const loadOrder = async (variant) => {
  const orderId = new URLSearchParams(window.location.search).get('order');
  if (!orderId) return;
  try {
    const order = await fetchJSON(`${API_BASE}/orders/${orderId}`);
    const codeEl = qs('[data-order-code]');
    if (!codeEl) return;
    codeEl.textContent = order.order_code || order.public_id;
    qs('[data-order-product]').textContent = order.product_name;
    qs('[data-order-price]').textContent = formatCurrency(order.price);
    qs('[data-order-buyer]').textContent = order.buyer_name;
    qs('[data-order-target]').textContent = order.buyer_target;
    qs('[data-order-note]').textContent = order.buyer_note || '-';

    if (variant === 'payment') {
      const config = await fetchJSON(`${API_BASE}/config`);
      qs('[data-payment-bsi]').textContent = config.bank_bsi || '-';
      qs('[data-payment-dana]').textContent = config.ewallet_dana || '-';
      qs('[data-payment-ovo]').textContent = config.ewallet_ovo || '-';
      const qris = qs('[data-payment-qris]');
      if (config.qris_url) {
        qris.src = config.qris_url;
        qris.alt = 'QRIS Maul Online Shop';
      }
    }
  } catch (error) {
    console.error(error);
  }
};

const confirmOrder = async () => {
  const orderId = new URLSearchParams(window.location.search).get('order');
  if (!orderId) return;
  try {
    const button = qs('[data-checkout-button]');
    if (button) button.disabled = true;
    const result = await fetchJSON(`${API_BASE}/orders/confirm`, {
      method: 'POST',
      body: JSON.stringify({ order_id: orderId }),
    });
    if (result.redirect_url) {
      window.location.href = result.redirect_url;
    } else {
      alert('Pesanan berhasil dikonfirmasi.');
    }
  } catch (error) {
    alert('Gagal konfirmasi pesanan.');
    console.error(error);
  }
};

const initCheckoutPage = async (variant) => {
  await loadOrder(variant);
  const button = qs('[data-checkout-button]');
  if (button) button.addEventListener('click', confirmOrder);
};

const init = async () => {
  initLoader();
  initTransitions();
  setActiveNav();
  await loadStoreStatus();

  const page = document.body.dataset.page;
  if (page === 'catalog') {
    await initCatalog();
  }
  if (page === 'checkout-payment') {
    await initCheckoutPage('payment');
  }
  if (page === 'checkout-confirm') {
    await initCheckoutPage('confirm');
  }
};

document.addEventListener('DOMContentLoaded', init);
