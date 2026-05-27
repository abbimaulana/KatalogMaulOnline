const SHEETS = {
  PRODUCTS: 'Products',
  ORDERS: 'Orders',
  CONFIG: 'Config',
};

const DEFAULT_CONFIG = {
  store_mode: 'auto',
  store_status: 'open',
  open_time: '08:00',
  close_time: '22:00',
  qris_url: '',
  bank_bsi: '',
  ewallet_dana: '',
  ewallet_ovo: '',
};

function doGet(e) {
  const action = e.parameter.action || '';
  try {
    switch (action) {
      case 'products':
        return jsonResponse(listProducts(e.parameter.mode));
      case 'product.get':
        return jsonResponse(getProduct(e.parameter.id));
      case 'order.get':
        return jsonResponse(getOrder(e.parameter.id));
      case 'config':
        return jsonResponse(getConfig());
      default:
        return jsonResponse({ error: 'Unknown action' }, 400);
    }
  } catch (err) {
    return jsonResponse({ error: err.message }, 500);
  }
}

function doPost(e) {
  const action = e.parameter.action || '';
  const payload = e.postData && e.postData.contents ? JSON.parse(e.postData.contents) : {};
  try {
    switch (action) {
      case 'product.create':
        return jsonResponse(createProduct(payload));
      case 'product.update':
        return jsonResponse(updateProduct(payload));
      case 'product.delete':
        return jsonResponse(deleteProduct(payload.id));
      case 'order.create':
        return jsonResponse(createOrder(payload));
      case 'order.confirm':
        return jsonResponse(confirmOrder(payload.order_id));
      case 'config.update':
        return jsonResponse(updateConfig(payload));
      case 'upload':
        return jsonResponse(uploadBase64(payload));
      default:
        return jsonResponse({ error: 'Unknown action' }, 400);
    }
  } catch (err) {
    return jsonResponse({ error: err.message }, 500);
  }
}

function jsonResponse(data, status) {
  const output = ContentService.createTextOutput(JSON.stringify(data));
  output.setMimeType(ContentService.MimeType.JSON);
  return output;
}

function getSheet(name, headers) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let sheet = ss.getSheetByName(name);
  if (!sheet) {
    sheet = ss.insertSheet(name);
    sheet.appendRow(headers);
  }
  const range = sheet.getRange(1, 1, 1, headers.length);
  const existing = range.getValues()[0];
  if (existing.join('') === '') {
    range.setValues([headers]);
  }
  return sheet;
}

function listProducts(mode) {
  const headers = ['id', 'code', 'name', 'description', 'price', 'image_url', 'is_direct_payment', 'is_active', 'created_at'];
  const sheet = getSheet(SHEETS.PRODUCTS, headers);
  const values = sheet.getDataRange().getValues();
  const items = values.slice(1).map((row) => mapRow(headers, row));
  const filtered = mode === 'admin' ? items : items.filter((item) => item.is_active === true);
  return { items: filtered };
}

function getProduct(productId) {
  const headers = ['id', 'code', 'name', 'description', 'price', 'image_url', 'is_direct_payment', 'is_active', 'created_at'];
  const sheet = getSheet(SHEETS.PRODUCTS, headers);
  const values = sheet.getDataRange().getValues();
  const items = values.slice(1).map((row) => mapRow(headers, row));
  const product = items.find((item) => item.id === productId);
  if (!product) throw new Error('Produk tidak ditemukan');
  return product;
}

function createProduct(payload) {
  const headers = ['id', 'code', 'name', 'description', 'price', 'image_url', 'is_direct_payment', 'is_active', 'created_at'];
  const sheet = getSheet(SHEETS.PRODUCTS, headers);
  const id = Utilities.getUuid();
  const code = `ITM-${padSequence(nextSequence('product_seq'), 4)}`;
  const row = [
    id,
    code,
    payload.name || '',
    payload.description || '',
    Number(payload.price || 0),
    payload.image_url || '',
    parseBoolean(payload.is_direct_payment),
    payload.is_active === undefined ? true : parseBoolean(payload.is_active),
    new Date().toISOString(),
  ];
  sheet.appendRow(row);
  return { id, code };
}

function updateProduct(payload) {
  const headers = ['id', 'code', 'name', 'description', 'price', 'image_url', 'is_direct_payment', 'is_active', 'created_at'];
  const sheet = getSheet(SHEETS.PRODUCTS, headers);
  const values = sheet.getDataRange().getValues();
  const index = values.findIndex((row, idx) => idx > 0 && row[0] === payload.id);
  if (index === -1) throw new Error('Produk tidak ditemukan');
  sheet.getRange(index + 1, 3).setValue(payload.name || '');
  sheet.getRange(index + 1, 4).setValue(payload.description || '');
  sheet.getRange(index + 1, 5).setValue(Number(payload.price || 0));
  sheet.getRange(index + 1, 6).setValue(payload.image_url || '');
  sheet.getRange(index + 1, 7).setValue(parseBoolean(payload.is_direct_payment));
  sheet.getRange(index + 1, 8).setValue(payload.is_active === undefined ? true : parseBoolean(payload.is_active));
  return { success: true };
}

function deleteProduct(productId) {
  const headers = ['id', 'code', 'name', 'description', 'price', 'image_url', 'is_direct_payment', 'is_active', 'created_at'];
  const sheet = getSheet(SHEETS.PRODUCTS, headers);
  const values = sheet.getDataRange().getValues();
  const index = values.findIndex((row, idx) => idx > 0 && row[0] === productId);
  if (index === -1) throw new Error('Produk tidak ditemukan');
  sheet.deleteRow(index + 1);
  return { success: true };
}

function createOrder(payload) {
  const product = getProduct(payload.product_id);
  const headers = ['id', 'order_code', 'product_id', 'product_name', 'price', 'buyer_name', 'buyer_contact', 'buyer_target', 'buyer_note', 'status', 'is_direct_payment', 'created_at'];
  const sheet = getSheet(SHEETS.ORDERS, headers);
  const id = `ORD-${padSequence(nextSequence('order_seq'), 6)}`;
  const row = [
    id,
    id,
    product.id,
    product.name,
    product.price,
    payload.buyer_name || '',
    payload.buyer_contact || '',
    payload.buyer_target || '',
    payload.buyer_note || '',
    'pending',
    product.is_direct_payment,
    new Date().toISOString(),
  ];
  sheet.appendRow(row);
  return {
    order_id: id,
    public_id: id,
    order_code: id,
    product_name: product.name,
    price: product.price,
    buyer_name: payload.buyer_name || '',
    buyer_contact: payload.buyer_contact || '',
    buyer_target: payload.buyer_target || '',
    buyer_note: payload.buyer_note || '',
    is_direct_payment: product.is_direct_payment,
    status: 'pending',
  };
}

function getOrder(orderId) {
  const headers = ['id', 'order_code', 'product_id', 'product_name', 'price', 'buyer_name', 'buyer_contact', 'buyer_target', 'buyer_note', 'status', 'is_direct_payment', 'created_at'];
  const sheet = getSheet(SHEETS.ORDERS, headers);
  const values = sheet.getDataRange().getValues();
  const items = values.slice(1).map((row) => mapRow(headers, row));
  const order = items.find((item) => item.id === orderId || item.order_code === orderId);
  if (!order) throw new Error('Order tidak ditemukan');
  return order;
}

function confirmOrder(orderId) {
  const headers = ['id', 'order_code', 'product_id', 'product_name', 'price', 'buyer_name', 'buyer_contact', 'buyer_target', 'buyer_note', 'status', 'is_direct_payment', 'created_at'];
  const sheet = getSheet(SHEETS.ORDERS, headers);
  const values = sheet.getDataRange().getValues();
  const index = values.findIndex((row, idx) => idx > 0 && (row[0] === orderId || row[1] === orderId));
  if (index === -1) throw new Error('Order tidak ditemukan');
  const isDirect = values[index][10] === true;
  const status = isDirect ? 'paid' : 'confirmed';
  sheet.getRange(index + 1, 10).setValue(status);
  const row = sheet.getRange(index + 1, 1, 1, headers.length).getValues()[0];
  return mapRow(headers, row);
}

function getConfig() {
  const sheet = getSheet(SHEETS.CONFIG, ['key', 'value']);
  const values = sheet.getDataRange().getValues();
  const config = { ...DEFAULT_CONFIG };
  values.slice(1).forEach((row) => {
    const key = row[0];
    if (key) {
      config[key] = row[1];
    }
  });
  return config;
}

function updateConfig(payload) {
  const sheet = getSheet(SHEETS.CONFIG, ['key', 'value']);
  const config = { ...getConfig(), ...payload };
  sheet.clearContents();
  sheet.appendRow(['key', 'value']);
  Object.keys(config).forEach((key) => {
    sheet.appendRow([key, config[key]]);
  });
  return { success: true };
}

function uploadBase64(payload) {
  if (!payload.data) throw new Error('File kosong');
  const folderId = PropertiesService.getScriptProperties().getProperty('DRIVE_FOLDER_ID');
  if (!folderId) throw new Error('DRIVE_FOLDER_ID belum disetel');
  const folder = DriveApp.getFolderById(folderId);
  const bytes = Utilities.base64Decode(payload.data);
  const blob = Utilities.newBlob(bytes, payload.content_type || 'application/octet-stream', payload.filename || 'file');
  const file = folder.createFile(blob);
  file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
  return { url: file.getUrl() };
}

function mapRow(headers, row) {
  const item = {};
  headers.forEach((header, index) => {
    if (header === 'is_direct_payment' || header === 'is_active') {
      item[header] = parseBoolean(row[index]);
    } else {
      item[header] = row[index];
    }
  });
  return item;
}

function nextSequence(key) {
  const props = PropertiesService.getScriptProperties();
  const current = Number(props.getProperty(key) || '0');
  const next = current + 1;
  props.setProperty(key, String(next));
  return next;
}

function padSequence(value, length) {
  return String(value).padStart(length, '0');
}

function parseBoolean(value) {
  if (typeof value === 'boolean') return value;
  if (typeof value === 'number') return value === 1;
  if (typeof value === 'string') {
    return ['true', 'yes', '1', 'aktif'].includes(value.toLowerCase());
  }
  return false;
}
