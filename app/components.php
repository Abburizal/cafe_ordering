<?php
/**
 * UI Components Library
 * Reusable UI components untuk konsistensi tampilan
 */

/**
 * Loading Overlay Component
 */
function loading_overlay() {
    return '
    <div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-2xl shadow-2xl text-center">
            <div class="animate-spin w-16 h-16 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-gray-700 font-semibold text-lg">Memproses...</p>
            <p class="text-gray-500 text-sm mt-2">Mohon tunggu sebentar</p>
        </div>
    </div>
    ';
}

/**
 * Loading Overlay with custom message
 */
function loading_overlay_custom($message = 'Memproses...', $submessage = 'Mohon tunggu sebentar') {
    return "
    <div id='loading-overlay' class='hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'>
        <div class='bg-white p-8 rounded-2xl shadow-2xl text-center'>
            <div class='animate-spin w-16 h-16 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto mb-4'></div>
            <p class='text-gray-700 font-semibold text-lg'>$message</p>
            <p class='text-gray-500 text-sm mt-2'>$submessage</p>
        </div>
    </div>
    ";
}

/**
 * Loading JS Functions
 */
function loading_scripts() {
    return "
    <script>
    function showLoading(message = 'Memproses...') {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('hidden');
        }
    }
    
    function hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }
    }
    
    // Auto show loading on form submit
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form[data-loading=\"true\"]');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                showLoading();
            });
        });
    });
    </script>
    ";
}

/**
 * Confirmation Modal Component
 */
function confirmation_modal() {
    return '
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6">
                <h3 id="confirm-title" class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Konfirmasi
                </h3>
            </div>
            <div class="p-6">
                <p id="confirm-message" class="text-gray-700 text-lg mb-6">Apakah Anda yakin?</p>
                <div class="flex gap-3">
                    <button id="confirm-cancel" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition font-semibold">
                        Batal
                    </button>
                    <button id="confirm-ok" class="flex-1 px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-semibold">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    ';
}

/**
 * Confirmation JS Functions
 */
function confirmation_scripts() {
    return "
    <script>
    let confirmCallback = null;
    
    function showConfirm(title, message, callback) {
        const modal = document.getElementById('confirm-modal');
        const titleEl = document.getElementById('confirm-title');
        const messageEl = document.getElementById('confirm-message');
        
        if (modal && titleEl && messageEl) {
            titleEl.innerHTML = '<svg class=\"w-8 h-8 mr-3\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\"/></svg>' + title;
            messageEl.textContent = message;
            modal.classList.remove('hidden');
            confirmCallback = callback;
        }
    }
    
    function hideConfirm() {
        const modal = document.getElementById('confirm-modal');
        if (modal) {
            modal.classList.add('hidden');
            confirmCallback = null;
        }
    }
    
    // Setup event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const cancelBtn = document.getElementById('confirm-cancel');
        const okBtn = document.getElementById('confirm-ok');
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', hideConfirm);
        }
        
        if (okBtn) {
            okBtn.addEventListener('click', function() {
                if (confirmCallback) {
                    confirmCallback();
                }
                hideConfirm();
            });
        }
        
        // Auto attach confirm to delete buttons
        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const message = this.getAttribute('data-confirm');
                const href = this.getAttribute('href');
                const form = this.closest('form');
                
                showConfirm('Konfirmasi Hapus', message, function() {
                    if (href) {
                        window.location.href = href;
                    } else if (form) {
                        form.submit();
                    }
                });
            });
        });
    });
    </script>
    ";
}

/**
 * Toast Notification Component
 */
function toast_container() {
    return '
    <div id="toast-container" class="fixed top-20 right-4 z-50 space-y-3"></div>
    ';
}

/**
 * Toast JS Functions
 */
function toast_scripts() {
    return "
    <script>
    function showToast(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const colors = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        };
        
        const icons = {
            'success': '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"/>',
            'error': '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\"/>',
            'warning': '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\"/>',
            'info': '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\"/>'
        };
        
        const toast = document.createElement('div');
        toast.className = colors[type] + ' text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 animate-slide-in';
        toast.innerHTML = `
            <svg class=\"w-6 h-6\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                ${icons[type]}
            </svg>
            <span class=\"font-semibold\">${message}</span>
            <button onclick=\"this.parentElement.remove()\" class=\"ml-4 hover:text-gray-200\">✕</button>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(400px)';
            toast.style.transition = 'all 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    </script>
    <style>
    @keyframes slide-in {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-slide-in { animation: slide-in 0.3s ease-out; }
    </style>
    ";
}

/**
 * Print Styles
 */
function print_styles() {
    return '
    <style media="print">
        @page {
            size: auto;
            margin: 10mm;
        }
        
        body {
            background: white !important;
            color: black !important;
        }
        
        .no-print {
            display: none !important;
        }
        
        .print-only {
            display: block !important;
        }
        
        /* Header styles */
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        /* Remove shadows and colors */
        * {
            box-shadow: none !important;
            text-shadow: none !important;
        }
        
        /* Force black text */
        h1, h2, h3, h4, h5, h6, p, span, td, th {
            color: black !important;
        }
    </style>
    ';
}

/**
 * Empty State Component
 */
function empty_state($icon, $title, $message, $action_url = null, $action_text = null) {
    $action_html = '';
    if ($action_url && $action_text) {
        $action_html = "<a href='$action_url' class='inline-block px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold'>$action_text</a>";
    }
    
    return "
    <div class='empty-state text-center py-16'>
        <i data-feather='$icon' class='w-24 h-24 text-gray-400 mx-auto mb-6 stroke-1'></i>
        <h3 class='text-2xl font-bold text-gray-700 mb-3'>$title</h3>
        <p class='text-gray-500 mb-6 text-lg'>$message</p>
        $action_html
    </div>
    ";
}

/**
 * Alert Component
 */
function alert($type, $message, $dismissible = true) {
    $colors = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];
    
    $icons = [
        'success' => 'check-circle',
        'error' => 'x-circle',
        'warning' => 'alert-triangle',
        'info' => 'info'
    ];
    
    $dismiss_btn = $dismissible ? '<button onclick="this.parentElement.remove()" class="ml-auto hover:opacity-70">✕</button>' : '';
    
    return "
    <div class='alert border-l-4 {$colors[$type]} p-4 rounded-lg flex items-center mb-4'>
        <i data-feather='{$icons[$type]}' class='w-5 h-5 mr-3'></i>
        <span class='flex-1'>$message</span>
        $dismiss_btn
    </div>
    ";
}

/**
 * Badge Component
 */
function badge($text, $color = 'gray') {
    $colors = [
        'gray' => 'bg-gray-200 text-gray-800',
        'red' => 'bg-red-200 text-red-800',
        'yellow' => 'bg-yellow-200 text-yellow-800',
        'green' => 'bg-green-200 text-green-800',
        'blue' => 'bg-blue-200 text-blue-800',
        'indigo' => 'bg-indigo-200 text-indigo-800',
        'purple' => 'bg-purple-200 text-purple-800',
        'pink' => 'bg-pink-200 text-pink-800'
    ];
    
    return "<span class='px-3 py-1 rounded-full text-sm font-semibold {$colors[$color]}'>$text</span>";
}
