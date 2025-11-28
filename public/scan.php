<?php
require_once __DIR__ . '/../config/config.php';

// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code Meja - Kantin Akademi MD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        #reader video {
            border-radius: 1rem;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Scan QR Code Meja</h1>
            <p class="text-gray-600">Arahkan kamera ke QR Code pada meja Anda</p>
        </div>

        <!-- Scanner Container -->
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-2xl mx-auto">
            <!-- QR Reader -->
            <div id="reader" class="mb-6"></div>
            
            <!-- Status Message -->
            <div id="status" class="text-center mb-4">
                <p class="text-gray-600">Memuat kamera...</p>
            </div>

            <!-- Result -->
            <div id="result" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <p class="font-semibold">Berhasil!</p>
                <p id="result-text" class="text-sm"></p>
            </div>

            <!-- Error -->
            <div id="error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <p class="font-semibold">Error!</p>
                <p id="error-text" class="text-sm"></p>
            </div>

            <!-- Manual Input Option -->
            <div class="text-center pt-4 border-t border-gray-200">
                <p class="text-gray-600 mb-3">Tidak bisa scan QR Code?</p>
                <a href="index.php" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300">
                    Pilih Meja Manual
                </a>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-8 max-w-2xl mx-auto bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Cara Menggunakan:
            </h3>
            <ol class="list-decimal list-inside text-blue-800 space-y-2">
                <li>Izinkan akses kamera saat diminta oleh browser</li>
                <li>Arahkan kamera ke QR Code yang ada di meja</li>
                <li>Tunggu hingga QR Code terbaca otomatis</li>
                <li>Anda akan diarahkan ke halaman menu</li>
            </ol>
        </div>
    </div>

    <script>
        let html5QrCode;
        let isScanning = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (isScanning) return; // Prevent multiple scans
            isScanning = true;

            console.log(`Scan result: ${decodedText}`, decodedResult);
            
            // Stop scanning
            html5QrCode.stop().then(() => {
                // Show success message
                document.getElementById('status').classList.add('hidden');
                document.getElementById('result').classList.remove('hidden');
                document.getElementById('result-text').textContent = 'Mengarahkan ke menu...';

                // Check if it's a full URL or just a code
                if (decodedText.includes('?code=')) {
                    // Full URL scanned
                    window.location.href = decodedText;
                } else if (decodedText.startsWith('TBL-')) {
                    // Just the code scanned
                    window.location.href = `index.php?code=${decodedText}`;
                } else {
                    // Try to redirect anyway
                    window.location.href = decodedText;
                }
            }).catch(err => {
                console.error('Error stopping scanner:', err);
                window.location.href = decodedText;
            });
        }

        function onScanError(errorMessage) {
            // Ignore scan errors (too many false positives)
            // console.warn(`QR scan error: ${errorMessage}`);
        }

        // Initialize QR Code Scanner
        function initScanner() {
            html5QrCode = new Html5Qrcode("reader");
            
            // Get cameras
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length) {
                    // Prefer back camera on mobile
                    let cameraId = cameras[0].id;
                    if (cameras.length > 1) {
                        // Try to find back camera
                        const backCamera = cameras.find(camera => 
                            camera.label.toLowerCase().includes('back') || 
                            camera.label.toLowerCase().includes('rear')
                        );
                        if (backCamera) {
                            cameraId = backCamera.id;
                        }
                    }

                    // Start scanning
                    html5QrCode.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        onScanSuccess,
                        onScanError
                    ).then(() => {
                        document.getElementById('status').innerHTML = '<p class="text-green-600 font-semibold">Kamera aktif - Arahkan ke QR Code</p>';
                    }).catch(err => {
                        document.getElementById('status').classList.add('hidden');
                        document.getElementById('error').classList.remove('hidden');
                        document.getElementById('error-text').textContent = `Gagal memulai kamera: ${err}`;
                    });
                } else {
                    document.getElementById('status').classList.add('hidden');
                    document.getElementById('error').classList.remove('hidden');
                    document.getElementById('error-text').textContent = 'Tidak ada kamera terdeteksi di perangkat Anda.';
                }
            }).catch(err => {
                document.getElementById('status').classList.add('hidden');
                document.getElementById('error').classList.remove('hidden');
                document.getElementById('error-text').textContent = `Error mengakses kamera: ${err}`;
            });
        }

        // Start scanner when page loads
        window.addEventListener('load', initScanner);

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (html5QrCode) {
                html5QrCode.stop().catch(err => console.error(err));
            }
        });
    </script>
</body>
</html>
