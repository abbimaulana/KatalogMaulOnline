const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET,POST,PUT,DELETE,OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

const jsonResponse = (data, status = 200) =>
  new Response(JSON.stringify(data), {
    status,
    headers: {
      'Content-Type': 'application/json',
      ...corsHeaders,
    },
  });

const parseBody = async (request) => {
  const contentType = request.headers.get('content-type') || '';
  if (contentType.includes('application/json')) {
    return request.json();
  }
  return {};
};

const gasGet = async (env, action, params = {}) => {
  const url = new URL(env.GAS_URL);
  url.searchParams.set('action', action);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, String(value));
    }
  });
  const response = await fetch(url.toString());
  if (!response.ok) {
    throw new Error(await response.text());
  }
  return response.json();
};

const gasPost = async (env, action, payload) => {
  const url = new URL(env.GAS_URL);
  url.searchParams.set('action', action);
  const response = await fetch(url.toString(), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!response.ok) {
    throw new Error(await response.text());
  }
  return response.json();
};

const formatOrderMessage = (order) => {
  return [
    `Pesanan Baru - ${order.order_code || order.public_id}`,
    `Produk: ${order.product_name}`,
    `Harga: Rp${order.price}`,
    `Nama: ${order.buyer_name}`,
    `Target: ${order.buyer_target}`,
    `Catatan: ${order.buyer_note || '-'}`,
    `Status: ${order.status || 'baru'}`,
  ].join('\n');
};

const sendWhatsApp = async (env, message) => {
  if (!env.WA_ACCESS_TOKEN || !env.WA_PHONE_ID || !env.WA_ADMIN_NUMBER) return;
  await fetch(`https://graph.facebook.com/v18.0/${env.WA_PHONE_ID}/messages`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: ['Bearer', env.WA_ACCESS_TOKEN].join(' '),
    },
    body: JSON.stringify({
      messaging_product: 'whatsapp',
      to: env.WA_ADMIN_NUMBER,
      type: 'text',
      text: { body: message },
    }),
  });
};

const sendTelegram = async (env, message) => {
  if (!env.TELEGRAM_TOKEN || !env.TELEGRAM_CHAT_ID) return;
  await fetch(`https://api.telegram.org/bot${env.TELEGRAM_TOKEN}/sendMessage`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      chat_id: env.TELEGRAM_CHAT_ID,
      text: message,
    }),
  });
};

const sendDiscord = async (env, message) => {
  if (!env.DISCORD_WEBHOOK_URL) return;
  await fetch(env.DISCORD_WEBHOOK_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ content: message }),
  });
};

const notifyAll = async (env, order) => {
  const message = formatOrderMessage(order);
  await Promise.all([
    sendWhatsApp(env, message),
    sendTelegram(env, message),
    sendDiscord(env, message),
  ]);
};

const computeStoreStatus = (config) => {
  const mode = config.store_mode || 'auto';
  const manualStatus = config.store_status || 'open';
  const openTime = config.open_time || '08:00';
  const closeTime = config.close_time || '22:00';

  if (mode === 'manual') {
    return {
      isOpen: manualStatus === 'open',
      message: manualStatus === 'open' ? 'Toko sedang buka.' : 'Maaf, Toko Sedang Tutup. Silakan kembali pada jam operasional.',
      open_time: openTime,
      close_time: closeTime,
      mode,
    };
  }

  const now = new Date();
  const formatter = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Asia/Jakarta',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  });
  const [hour, minute] = formatter.format(now).split(':').map(Number);
  const current = hour * 60 + minute;

  const [openHour, openMinute] = openTime.split(':').map(Number);
  const [closeHour, closeMinute] = closeTime.split(':').map(Number);
  const openMinutes = openHour * 60 + openMinute;
  const closeMinutes = closeHour * 60 + closeMinute;

  let isOpen = false;
  if (openMinutes < closeMinutes) {
    isOpen = current >= openMinutes && current <= closeMinutes;
  } else {
    isOpen = current >= openMinutes || current <= closeMinutes;
  }

  return {
    isOpen,
    message: isOpen ? 'Toko sedang buka.' : 'Maaf, Toko Sedang Tutup. Silakan kembali pada jam operasional.',
    open_time: openTime,
    close_time: closeTime,
    mode,
  };
};

const handleRequest = async (request, env) => {
  if (request.method === 'OPTIONS') {
    return new Response(null, { headers: corsHeaders });
  }

  const url = new URL(request.url);
  if (!url.pathname.startsWith('/api')) {
    return new Response('Not Found', { status: 404 });
  }

  if (!env.GAS_URL) {
    return jsonResponse({ error: 'GAS_URL belum dikonfigurasi.' }, 500);
  }

  const path = url.pathname.replace('/api', '') || '/';
  const segments = path.split('/').filter(Boolean);
  const method = request.method.toUpperCase();

  try {
    if (segments[0] === 'products') {
      if (method === 'GET') {
        const mode = url.searchParams.get('mode');
        const data = await gasGet(env, 'products', { mode });
        return jsonResponse(data);
      }
      if (method === 'POST') {
        const payload = await parseBody(request);
        const data = await gasPost(env, 'product.create', payload);
        return jsonResponse(data);
      }
      if (segments[1]) {
        const productId = segments[1];
        if (method === 'GET') {
          const data = await gasGet(env, 'product.get', { id: productId });
          return jsonResponse(data);
        }
        if (method === 'PUT') {
          const payload = await parseBody(request);
          const data = await gasPost(env, 'product.update', { id: productId, ...payload });
          return jsonResponse(data);
        }
        if (method === 'DELETE') {
          const data = await gasPost(env, 'product.delete', { id: productId });
          return jsonResponse(data);
        }
      }
    }

    if (segments[0] === 'orders') {
      if (method === 'POST' && segments[1] === 'confirm') {
        const payload = await parseBody(request);
        const data = await gasPost(env, 'order.confirm', payload);
        await notifyAll(env, data.order || data);
        const waBot = env.WA_CS_NUMBER || '6287872369848';
        const message = encodeURIComponent(formatOrderMessage(data.order || data));
        return jsonResponse({
          success: true,
          redirect_url: `https://wa.me/${waBot}?text=${message}`,
        });
      }
      if (method === 'POST') {
        const payload = await parseBody(request);
        const data = await gasPost(env, 'order.create', payload);
        return jsonResponse(data);
      }
      if (method === 'GET' && segments[1]) {
        const data = await gasGet(env, 'order.get', { id: segments[1] });
        return jsonResponse(data);
      }
    }

    if (segments[0] === 'config' && method === 'GET') {
      const data = await gasGet(env, 'config');
      return jsonResponse(data);
    }

    if (segments[0] === 'store-status' && method === 'GET') {
      const config = await gasGet(env, 'config');
      const status = computeStoreStatus(config);
      return jsonResponse(status);
    }

    if (segments[0] === 'admin' && segments[1] === 'config' && method === 'POST') {
      const payload = await parseBody(request);
      const data = await gasPost(env, 'config.update', payload);
      return jsonResponse(data);
    }

    if (segments[0] === 'admin' && segments[1] === 'upload' && method === 'POST') {
      const payload = await parseBody(request);
      const data = await gasPost(env, 'upload', payload);
      return jsonResponse(data);
    }

    return new Response('Not Found', { status: 404 });
  } catch (error) {
    return jsonResponse({ error: error.message || 'Server Error' }, 500);
  }
};

export default {
  fetch: handleRequest,
};
