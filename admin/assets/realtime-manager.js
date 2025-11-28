/**
 * Real-time Order Management System
 * Menangani polling real-time untuk dashboard admin dan customer
 * Usage: include di HTML dan gunakan RealtimeOrderManager class
 */

class RealtimeOrderManager {
    constructor(config = {}) {
        this.config = {
            pollInterval: config.pollInterval || 3000, // 3 detik
            notificationEnabled: config.notificationEnabled !== false,
            soundEnabled: config.soundEnabled !== false,
            debug: config.debug || false,
            ...config
        };
        
        this.pollingTimer = null;
        this.lastUpdate = null;
        this.knownOrders = new Map();
        this.listeners = {
            onOrderUpdate: config.onOrderUpdate || (() => {}),
            onOrderStatusChange: config.onOrderStatusChange || (() => {}),
            onNewOrder: config.onNewOrder || (() => {}),
            onError: config.onError || (() => {})
        };
    }
    
    /**
     * Mulai polling
     * @param {string} apiEndpoint - URL API endpoint
     * @param {object} params - Query parameters untuk API call
     */
    start(apiEndpoint, params = {}) {
        this.log('Memulai polling...');
        this.apiEndpoint = apiEndpoint;
        this.params = params;
        
        // Polling pertama kali
        this.poll();
        
        // Setup interval
        this.pollingTimer = setInterval(() => this.poll(), this.config.pollInterval);
    }
    
    /**
     * Hentikan polling
     */
    stop() {
        if (this.pollingTimer) {
            clearInterval(this.pollingTimer);
            this.pollingTimer = null;
            this.log('Polling dihentikan');
        }
    }
    
    /**
     * Lakukan polling ke server
     */
    async poll() {
        try {
            const url = new URL(this.apiEndpoint, window.location.origin);
            Object.keys(this.params).forEach(key => {
                url.searchParams.append(key, this.params[key]);
            });
            
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.handleUpdate(data);
            } else {
                this.log('API returned error: ' + data.error, 'error');
                this.listeners.onError(data.error);
            }
        } catch (error) {
            this.log('Polling error: ' + error.message, 'error');
            this.listeners.onError(error.message);
        }
    }
    
    /**
     * Handle data update dari server
     * @param {object} data - Data dari API
     */
    handleUpdate(data) {
        const orders = data.order ? [data.order] : (data.orders || []);
        
        if (orders.length === 0) {
            this.log('Tidak ada order dari server');
            return;
        }
        
        this.lastUpdate = new Date();
        
        orders.forEach(order => {
            const orderId = order.id;
            const previousOrder = this.knownOrders.get(orderId);
            
            // Deteksi perubahan status
            if (previousOrder && previousOrder.status !== order.status) {
                this.log(`Order #${order.order_code}: Status berubah dari ${previousOrder.status} → ${order.status}`);
                
                this.notifyStatusChange(order, previousOrder.status);
                this.listeners.onOrderStatusChange({
                    orderId,
                    orderCode: order.order_code,
                    oldStatus: previousOrder.status,
                    newStatus: order.status,
                    order
                });
            }
            
            // Deteksi order baru
            if (!previousOrder) {
                this.log(`Order baru terdeteksi: #${order.order_code}`);
                
                if (this.config.notificationEnabled) {
                    this.notifyNewOrder(order);
                }
                this.listeners.onNewOrder(order);
            }
            
            // Update order di memory
            this.knownOrders.set(orderId, order);
        });
        
        // Trigger general update listener
        this.listeners.onOrderUpdate({
            orders,
            timestamp: new Date(),
            lastUpdate: this.lastUpdate
        });
    }
    
    /**
     * Tampilkan notifikasi untuk order baru
     * @param {object} order - Order data
     */
    notifyNewOrder(order) {
        if (this.config.soundEnabled) {
            this.playNotificationSound();
        }
        
        if ('Notification' in window && Notification.permission === 'granted') {
            const items = order.items 
                ? order.items.map(i => `${i.qty}x ${i.product_name}`).join(', ')
                : 'N/A';
            
            new Notification('🔔 PESANAN BARU!', {
                body: `${order.order_code} - Meja: ${order.table_name || 'N/A'}\n${items}`,
                icon: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwYXRoIGQ9Ik0xOCA4QTYgNiAwIDAwNiA4YzAgNy0zIDktMyA5aDE4cy0zLTItMy05TTEzLjczIDIxYTIgMiAwIDAxLTMuNDYgMCIvPjwvc3ZnPg==',
                tag: 'new-order-' + order.id
            });
        }
    }
    
    /**
     * Tampilkan notifikasi untuk perubahan status
     * @param {object} order - Order data
     * @param {string} oldStatus - Status sebelumnya
     */
    notifyStatusChange(order, oldStatus) {
        const statusLabels = {
            'pending': 'Menunggu Konfirmasi',
            'processing': 'Sedang Diproses',
            'done': 'Selesai',
            'cancelled': 'Dibatalkan'
        };
        
        if (this.config.soundEnabled) {
            this.playNotificationSound();
        }
        
        if ('Notification' in window && Notification.permission === 'granted') {
            const title = oldStatus === 'pending' ? '⏳ Status Diperbarui!' : '✅ Status Pesanan Berubah!';
            const body = `${order.order_code}\n${statusLabels[oldStatus] || oldStatus} → ${statusLabels[order.status] || order.status}`;
            
            new Notification(title, {
                body,
                tag: 'status-change-' + order.id
            });
        }
    }
    
    /**
     * Mainkan suara notifikasi
     */
    playNotificationSound() {
        try {
            // Coba gunakan existing audio element
            const audio = document.getElementById('notificationSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(e => this.log('Audio play failed: ' + e.message, 'warn'));
            } else {
                // Fallback: buat audio element baru dengan simple beep
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            }
        } catch (error) {
            this.log('Sound notification failed: ' + error.message, 'warn');
        }
    }
    
    /**
     * Request notification permission
     */
    async requestNotificationPermission() {
        if (!('Notification' in window)) {
            this.log('Browser tidak mendukung Notification API');
            return false;
        }
        
        if (Notification.permission === 'granted') {
            return true;
        }
        
        if (Notification.permission !== 'denied') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }
        
        return false;
    }
    
    /**
     * Get order by ID
     * @param {number} orderId - Order ID
     * @returns {object|null}
     */
    getOrder(orderId) {
        return this.knownOrders.get(orderId) || null;
    }
    
    /**
     * Get all known orders
     * @returns {array}
     */
    getAllOrders() {
        return Array.from(this.knownOrders.values());
    }
    
    /**
     * Logging utility
     * @param {string} message - Message to log
     * @param {string} level - Log level (info, warn, error)
     */
    log(message, level = 'info') {
        if (this.config.debug) {
            const timestamp = new Date().toLocaleTimeString('id-ID');
            console.log(`[RealtimeOrderManager ${timestamp}] [${level.toUpperCase()}] ${message}`);
        }
    }
    
    /**
     * Get last update timestamp
     * @returns {Date|null}
     */
    getLastUpdate() {
        return this.lastUpdate;
    }
}

// Export untuk CommonJS dan ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RealtimeOrderManager;
}
