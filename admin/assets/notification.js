/**
 * Notification Handler for Admin Dashboard
 * Auto-refresh orders dan notifikasi pesanan baru
 */

class NotificationManager {
    constructor(options = {}) {
        this.checkInterval = options.checkInterval || 10000;
        this.soundEnabled = options.soundEnabled !== false;
        this.lastOrderId = 0;
        this.intervalId = null;
        
        this.init();
    }
    
    init() {
        this.loadLastOrderId();
        this.startChecking();
        this.requestNotificationPermission();
    }
    
    loadLastOrderId() {
        const stored = localStorage.getItem('lastOrderId');
        if (stored) {
            this.lastOrderId = parseInt(stored);
        } else {
            // Jika belum ada, ambil order ID terakhir dari server saat init
            this.fetchLatestOrderId();
        }
    }
    
    async fetchLatestOrderId() {
        try {
            const response = await fetch('api/cek_pesanan_baru.php?init=1');
            const data = await response.json();
            if (data.latest_order_id) {
                this.saveLastOrderId(data.latest_order_id);
            }
        } catch (error) {
            console.error('Error fetching latest order ID:', error);
        }
    }
    
    saveLastOrderId(orderId) {
        this.lastOrderId = orderId;
        localStorage.setItem('lastOrderId', orderId);
    }
    
    async requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission();
        }
    }
    
    startChecking() {
        this.checkNewOrders();
        
        this.intervalId = setInterval(() => {
            this.checkNewOrders();
        }, this.checkInterval);
    }
    
    stopChecking() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }
    
    async checkNewOrders() {
        try {
            const response = await fetch('api/cek_pesanan_baru.php?last_id=' + this.lastOrderId);
            const data = await response.json();
            
            if (data.ada_pesanan_baru && data.pesanan_baru.length > 0) {
                // Simpan ID tertinggi SEBELUM menampilkan notifikasi
                const maxId = Math.max(...data.pesanan_baru.map(o => o.id));
                this.saveLastOrderId(maxId);
                
                // Tampilkan notifikasi untuk setiap order baru
                data.pesanan_baru.forEach(order => {
                    this.showNotification(order);
                });
                
                // Play sound sekali saja
                if (this.soundEnabled) {
                    this.playNotificationSound();
                }
                
                // Reload order list tanpa reload halaman
                this.reloadOrderList();
            }
            
        } catch (error) {
            console.error('Error checking new orders:', error);
        }
    }
    
    showNotification(order) {
        const title = '🔔 Pesanan Baru!';
        const message = `Meja: ${order.table_name || 'N/A'}\nTotal: Rp ${this.formatRupiah(order.total || 0)}`;
        
        // Browser notification (jika diizinkan)
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                tag: 'order-' + order.id, // Prevent duplicate
                requireInteraction: false // Auto close after a few seconds
            });
            
            notification.onclick = () => {
                window.focus();
                window.location.href = 'orders_detail.php?id=' + order.id;
                notification.close();
            };
            
            // Auto close after 5 seconds
            setTimeout(() => notification.close(), 5000);
        }
        
        // In-page notification
        this.showInPageNotification(order);
    }
    
    showInPageNotification(order) {
        const message = `Meja: ${order.table_name || 'N/A'} - Total: Rp ${this.formatRupiah(order.total || 0)}`;
        
        // Check if notification already exists
        const existingNotif = document.querySelector(`[data-order-id="${order.id}"]`);
        if (existingNotif) {
            return; // Don't show duplicate
        }
        
        const notif = document.createElement('div');
        notif.className = 'fixed top-20 right-4 bg-orange-500 text-white px-6 py-4 rounded-lg shadow-2xl z-50 animate-bounce';
        notif.setAttribute('data-order-id', order.id);
        notif.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="text-2xl">🔔</span>
                <div>
                    <div class="font-bold">Pesanan Baru!</div>
                    <div class="text-sm">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 font-bold text-xl">✕</button>
            </div>
        `;
        
        document.body.appendChild(notif);
        
        // Auto remove after 8 seconds
        setTimeout(() => {
            if (notif.parentElement) {
                notif.style.opacity = '0';
                notif.style.transform = 'translateX(400px)';
                notif.style.transition = 'all 0.3s ease-out';
                setTimeout(() => notif.remove(), 300);
            }
        }, 8000);
    }
    
    playNotificationSound() {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj==');
        audio.play().catch(e => console.log('Sound play failed:', e));
    }
    
    reloadOrderList() {
        // Jangan reload halaman, tapi reload konten tabel via AJAX
        if (typeof reloadOrders === 'function') {
            reloadOrders();
        } else {
            // Fallback: reload tabel orders via fetch
            this.updateOrderTable();
        }
    }
    
    async updateOrderTable() {
        try {
            // Fetch updated orders without page reload
            const response = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            
            // Parse and update only the table
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('table');
            const currentTable = document.querySelector('table');
            
            if (newTable && currentTable) {
                currentTable.innerHTML = newTable.innerHTML;
                // Reinitialize feather icons
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        } catch (error) {
            console.error('Error updating order table:', error);
        }
    }
    
    formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }
}

if (window.location.pathname.includes('dashboard.php') || window.location.pathname.includes('orders.php')) {
    const notificationManager = new NotificationManager({
        checkInterval: 10000,
        soundEnabled: true
    });
    
    window.notificationManager = notificationManager;
}
