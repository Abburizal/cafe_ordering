/**
 * Admin Notification System
 * Menampilkan toast notification dan sound saat ada order baru
 */

// Sound notification
let notificationSound = null;

// Inisialisasi sound
function initNotificationSound() {
    notificationSound = new Audio();
    // Simple notification beep
    notificationSound.src = 'data:audio/mpeg;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA//tQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAACAAADhAC7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7//////////////////////////////////////////////////////////////////8AAAAATGF2YzU4LjEzAAAAAAAAAAAAAAAAJAQKAAAAAAAAA4SXUQxdAAAAAAD/+xDEAAPAAAGkAAAAIAAANIAAAARMQU1FMy4xMDBVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
    notificationSound.volume = 0.7;
}

// Toast notification
function showToast(message, type = 'success') {
    const oldToast = document.getElementById('notification-toast');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.id = 'notification-toast';
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-2xl transform transition-all duration-300 ${
        type === 'success' ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-blue-500 to-blue-600'
    } text-white flex items-center space-x-3 max-w-md`;
    
    toast.style.animation = 'slideIn 0.3s ease-out';
    
    toast.innerHTML = `
        <div class="bg-white/20 rounded-full p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
        </div>
        <div class="flex-1">
            <p class="font-bold text-lg">🎉 Pesanan Baru!</p>
            <p class="text-sm opacity-90">${message}</p>
        </div>
        <button onclick="closeToast()" class="hover:bg-white/20 rounded-full p-1 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;

    document.body.appendChild(toast);
    playNotificationSound();

    setTimeout(() => closeToast(), 8000);
}

function playNotificationSound() {
    if (notificationSound) {
        notificationSound.currentTime = 0;
        notificationSound.play().catch(err => console.log('Sound play error:', err));
    }
}

function closeToast() {
    const toast = document.getElementById('notification-toast');
    if (toast) {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }
}

// Order monitoring
let lastOrderId = 0;
let checkInterval = null;

function startOrderMonitoring() {
    fetchLastOrderId();
    checkInterval = setInterval(checkNewOrders, 5000); // Check every 5 seconds
}

function fetchLastOrderId() {
    fetch('api/get_last_order_id.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) lastOrderId = data.last_id || 0;
        })
        .catch(err => console.error('Error fetching last order ID:', err));
}

function checkNewOrders() {
    fetch(`api/check_new_orders.php?last_id=${lastOrderId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.new_orders > 0) {
                lastOrderId = data.current_last_id;
                const message = `${data.new_orders} order dari ${data.table_name || 'Customer'}`;
                showToast(message, 'success');
                
                // Reload if function exists
                if (typeof reloadOrderList === 'function') {
                    setTimeout(() => reloadOrderList(), 1000);
                }
            }
        })
        .catch(err => console.error('Error checking orders:', err));
}

function stopOrderMonitoring() {
    if (checkInterval) clearInterval(checkInterval);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initNotificationSound();
    if (document.body.classList.contains('admin-page') || window.location.pathname.includes('admin')) {
        startOrderMonitoring();
    }
});

window.addEventListener('beforeunload', stopOrderMonitoring);

// CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);
