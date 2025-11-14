<?php
// Minimal server-side bootstrap (untuk konsistensi jika perlu)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Pesanan - RestoKu</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap'); body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-orange-50 min-h-screen p-6">
  <div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-indigo-700 flex items-center">
        <i data-feather="clock" class="w-6 h-6 mr-2"></i>
        Riwayat Pesanan
      </h1>
      <div class="space-x-2">
        <a href="menu.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Kembali ke Menu</a>
        <button id="clearHistory" class="px-3 py-2 border rounded-lg text-sm">Hapus Riwayat</button>
      </div>
    </div>

    <div id="content" class="bg-white p-6 rounded-xl shadow-lg">
      <p id="empty" class="text-center text-gray-600">Memuat riwayat...</p>
      <div id="list" class="space-y-4"></div>
    </div>
  </div>

<script>
feather.replace();

// Ambil list ID dari localStorage, panggil API, render hasil
(async function(){
  const key = 'riwayat_pesanan_cafe';
  const emptyEl = document.getElementById('empty');
  const listEl = document.getElementById('list');

  let ids = JSON.parse(localStorage.getItem(key) || '[]').filter(Boolean);
  if (!ids.length) {
    emptyEl.textContent = 'Riwayat pesanan tidak ditemukan.';
    return;
  }

  emptyEl.textContent = 'Mengambil data pesanan...';

  try {
    const resp = await fetch('api/get_orders.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ids: ids })
    });
    if (!resp.ok) throw new Error('Response not ok');
    const data = await resp.json();

    if (!Array.isArray(data.orders) || !data.orders.length) {
      emptyEl.textContent = 'Riwayat pesanan kosong atau tidak tersedia.';
      return;
    }

    emptyEl.style.display = 'none';

    // render setiap order
    data.orders.forEach(order => {
      const div = document.createElement('div');
      div.className = 'p-4 border rounded-lg bg-gray-50';
      
      div.innerHTML = `
        <div class="flex justify-between items-center mb-2">
          <div>
            <div class="text-sm text-gray-600">Kode Order</div>
            <div class="font-bold text-lg text-indigo-800">${escapeHtml(order.order_code)}</div>
            <div class="text-sm text-gray-500">Meja: ${escapeHtml(order.table_id || '')} · ${new Date(order.created_at).toLocaleString()}</div>
          </div>
          <div class="text-right
