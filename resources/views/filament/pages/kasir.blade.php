<x-filament::page>
    <div class="p-6 bg-gray-100 dark:bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md dark:shadow-gray-700">
            @if (session('message'))
                <div class="bg-green-500 dark:bg-green-600 text-white p-3 rounded mb-4 text-center">
                    {{ session('message') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="bg-red-500 dark:bg-red-600 text-white p-3 rounded mb-4 text-center">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Loading State Overlay -->
          <div wire:loading wire:target="addToCart, updateQuantity, removeItem, checkout" class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center z-50 overflow-hidden" style="margin-top: 50px;">
              <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-md w-full mx-auto flex flex-col items-center">
                  <div id="loadingSpinner" class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500 dark:border-blue-400 mb-4"></div>
                  <div id="successCheckmark" class="hidden mb-4">
                      <svg class="h-16 w-16 text-green-500 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                  </div>
                  <p id="loadingText" class="text-gray-700 dark:text-gray-300 text-lg font-medium text-center">Memproses...</p>
                  <p id="successText" class="hidden text-gray-700 dark:text-gray-300 text-lg font-medium text-center">Selesai!</p>
              </div>
          </div>

            {{-- Bagian Input & Kamera --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="flex gap-2">
                    <input type="text" id="barcodeInput" wire:model="barcode" wire:keydown.enter="addToCart"
                           placeholder="Scan / Ketik Barcode" 
                           class="border dark:border-gray-600 p-3 rounded w-full focus:ring focus:ring-blue-300 dark:focus:ring-blue-500 
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                {{-- Kamera Scanner - now visible by default --}}
                <div id="scanner-container" class="border dark:border-gray-600 p-2 rounded-lg bg-gray-200 dark:bg-gray-700 relative">
                    <video id="scanner" class="w-full h-auto"></video>
                    <button id="closeScanner" class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 text-xs rounded shadow-md hover:bg-red-600 transition">Tutup</button>
                </div>
            </div>

            {{-- Tabel Keranjang --}}
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border dark:border-gray-600 rounded-lg shadow-sm">
                    <thead>
                        <tr class="bg-blue-500 dark:bg-blue-600 text-white">
                            <th class="border dark:border-gray-600 p-3">Nama</th>
                            <th class="border dark:border-gray-600 p-3">Harga</th>
                            <th class="border dark:border-gray-600 p-3">Jumlah</th>
                            <th class="border dark:border-gray-600 p-3">Subtotal</th>
                            <th class="border dark:border-gray-600 p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cart as $index => $item)
                            <tr class="bg-white dark:bg-gray-700 border-b dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <td class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">{{ $item['nama'] }}</td>
                                <td class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">Rp{{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td class="border dark:border-gray-600 p-3">
                                    <input type="number" wire:model="cart.{{ $index }}.jumlah"
                                           wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)"
                                           min="1" class="w-16 border dark:border-gray-600 p-2 rounded text-center 
                                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                </td>
                                <td class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                <td class="border dark:border-gray-600 p-3">
                                    <button wire:click="removeItem({{ $item['id'] }})" 
                                            class="bg-red-500 dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100 p-2 rounded shadow-md hover:bg-red-600 dark:hover:bg-red-700 transition">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border dark:border-gray-600 p-4 text-center text-gray-500 dark:text-gray-400">
                                    Keranjang belanja kosong
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-600 font-bold">
                            <td colspan="3" class="border dark:border-gray-600 p-3 text-right text-gray-900 dark:text-gray-100">Total:</td>
                            <td class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">
                                Rp{{ number_format(collect($cart)->sum('subtotal'), 0, ',', '.') }}
                            </td>
                            <td class="border dark:border-gray-600 p-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Tombol Checkout --}}
            <button id="checkoutButton" wire:click="checkout" 
                    class="w-full p-3 rounded mt-6 shadow-md transition 
                           {{ count($cart) > 0 ? 'bg-green-500 dark:bg-green-600 dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100 hover:bg-green-600 dark:hover:bg-green-700' : 'bg-gray-400 dark:bg-gray-500 text-white cursor-not-allowed' }}"
                    {{ count($cart) > 0 ? '' : 'disabled' }}>
                <span wire:loading.remove wire:target="checkout">Checkout</span>
                <span wire:loading wire:target="checkout" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>

            {{-- Tabel Produk dengan Filament Live Search --}}
            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Daftar Produk</h2>
                    
                    {{-- Kolom Pencarian Filament --}}
                    <div class="w-64">
                        {{ $this->form }}
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border dark:border-gray-600 rounded-lg shadow-sm">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-700">
                                <th class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">Nama Produk</th>
                                <th class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">Gambar</th>
                                <th class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">Harga</th>
                                <th class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <td class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">{{ $product->nama }}</td>
                                    <td class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100"><img src="{{ Storage::url($product->gambar) }}" class="h-16 w-16 object-cover"></td>
                                    <td class="border dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100">Rp{{ number_format($product->harga, 0, ',', '.') }}</td>
                                    <td class="border dark:border-gray-600 p-3">
                                        <button wire:click="addToCart({{ $product->id }})" 
                                                class="bg-blue-500 dark:border-gray-600 p-3 text-gray-900 dark:text-gray-100 p-2 rounded shadow-md hover:bg-blue-600 dark:hover:bg-blue-700 transition">
                                            <span wire:loading.remove wire:target="addToCart({{ $product->id }})">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l1 6h13l1-6h2M5 9h14l-1 8H6l-1-8m2 8a2 2 0 104 0m6 0a2 2 0 104 0" />
                                                </svg>
                                            </span>
                                            <span wire:loading wire:target="addToCart({{ $product->id }})" class="flex items-center justify-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Menambahkan...
                                            </span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="border dark:border-gray-600 p-4 text-center text-gray-500 dark:text-gray-400">
                                        {{ empty($search) ? 'Tidak ada produk tersedia' : 'Tidak ada produk yang cocok dengan pencarian "' . $search . '"' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    

    {{-- Script Scanner dengan Kamera --}}

    {{-- Script Scanner dengan Kamera - PERBAIKAN LENGKAP --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced loading and success animations
    function showLoadingSuccess() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const successCheckmark = document.getElementById('successCheckmark');
        const loadingText = document.getElementById('loadingText');
        const successText = document.getElementById('successText');
        
        if (!loadingSpinner || !successCheckmark || !loadingText || !successText) {
            console.error('Loading animation elements not found');
            return;
        }
        
        // Hide loading spinner and text, show success checkmark and text
        loadingSpinner.classList.add('hidden');
        successCheckmark.classList.remove('hidden');
        loadingText.classList.add('hidden');
        successText.classList.remove('hidden');
        
        // Add animation to the checkmark for better visibility
        successCheckmark.classList.add('animate-bounce');
        
        // Reset back to loading state after delay (for next operation)
        setTimeout(() => {
            loadingSpinner.classList.remove('hidden');
            successCheckmark.classList.add('hidden');
            successCheckmark.classList.remove('animate-bounce');
            loadingText.classList.remove('hidden');
            successText.classList.add('hidden');
        }, 2000); // Show success for 2 seconds
    }
    
    // Listen for all Livewire events that indicate completion
    if (window.Livewire) {
        // Add all events that should trigger the success animation
        const successEvents = [
            'productAdded',
            'checkoutCompleted',
            'quantityUpdated',
            'itemRemoved'
        ];
        
        // Register listeners for all success events
        successEvents.forEach(eventName => {
            window.Livewire.on(eventName, function() {
                showLoadingSuccess();
            });
        });
        
        // Listen for product added event
        window.Livewire.on('productAdded', (message) => {
            // Show success checkmark animation
            showLoadingSuccess();
            
            // If product successfully added, just reset the input but keep scanner active
            const barcodeInput = document.getElementById('barcodeInput');
            if (barcodeInput) {
                barcodeInput.value = '';
                if (barcodeInput._x_model) {
                    barcodeInput._x_model.set('');
                }
            }

            // Update status indicator if it exists
            const statusBox = document.getElementById('scannerStatus');
            if (statusBox) {
                statusBox.textContent = message || 'Produk berhasil ditambahkan';
                statusBox.style.backgroundColor = 'rgba(0,128,0,0.7)';

                setTimeout(() => {
                    if (statusBox && statusBox.parentNode) {
                        statusBox.textContent = 'Siap scan berikutnya, kamera tetap aktif...';
                        statusBox.style.backgroundColor = 'rgba(0,0,0,0.7)';
                    }
                }, 3000);
            }
        });

        // Listen for scan error event
        window.Livewire.on('scanError', (message) => {
            // Display error message if any
            const statusBox = document.getElementById('scannerStatus');
            if (statusBox) {
                statusBox.textContent = message || 'Error saat memindai produk';
                statusBox.style.backgroundColor = 'rgba(220,0,0,0.7)';

                setTimeout(() => {
                    if (statusBox && statusBox.parentNode) {
                        statusBox.textContent = 'Siap scan berikutnya, kamera tetap aktif...';
                        statusBox.style.backgroundColor = 'rgba(0,0,0,0.7)';
                    }
                }, 3000);
            }
        });
        
        // Listen for receipt printing
        window.Livewire.on('printReceipt', function(transactionId) {
            // Show success animation first
            showLoadingSuccess();
            
            // Then open receipt in new tab
            window.open('/receipt/print/' + transactionId, '_blank', 'width=800,height=600');
        });
    }
    
    // Deteksi mode gelap
    const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Fungsi untuk menyesuaikan scanner overlay berdasarkan mode
    function updateScannerForDarkMode(isDark) {
        const overlay = document.getElementById('scanOverlay');
        if (overlay) {
            overlay.style.border = isDark ? '2px solid #FF3333' : '2px solid #FF0000';
        }
        
        const targetArea = overlay ? overlay.querySelector('div') : null;
        if (targetArea) {
            targetArea.style.border = isDark ? '2px dashed #33FF33' : '2px dashed #00FF00';
        }
        
        const statusBox = document.getElementById('scannerStatus');
        if (statusBox) {
            statusBox.style.backgroundColor = isDark ? 'rgba(0,0,0,0.8)' : 'rgba(0,0,0,0.7)';
        }
    }
    
    // Tambahkan listener untuk perubahan mode gelap/terang
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        updateScannerForDarkMode(e.matches);
    });
    
    // Verifikasi bahwa Quagga library dimuat
    const checkQuaggaLoaded = () => {
        if (typeof Quagga === 'undefined') {
            console.error("Quagga library tidak dimuat");
            const scannerContainer = document.getElementById('scanner-container');
            if (scannerContainer) {
                const errorBox = document.createElement('div');
                errorBox.style.position = 'absolute';
                errorBox.style.inset = '0';
                errorBox.style.display = 'flex';
                errorBox.style.alignItems = 'center';
                errorBox.style.justifyContent = 'center';
                errorBox.style.backgroundColor = 'rgba(0,0,0,0.8)';
                errorBox.style.color = 'white';
                errorBox.style.textAlign = 'center';
                errorBox.style.padding = '20px';
                errorBox.style.fontSize = '16px';
                errorBox.innerHTML = '<div>Library scanner barcode tidak dapat dimuat.<br>Periksa koneksi internet Anda dan coba muat ulang halaman.</div>';
                scannerContainer.appendChild(errorBox);
            }
            return false;
        }
        return true;
    };
    
    // Get required elements
    const scannerContainer = document.getElementById('scanner-container');
    const scannerVideo = document.getElementById('scanner');
    const closeScannerBtn = document.getElementById('closeScanner');
    const barcodeInput = document.getElementById('barcodeInput');
    
    // Variables for scanner state
    let scannerActive = false;
    let mediaStream = null;
    let lastScannedCode = null;
    let lastScanTime = 0;
    const scanCooldown = 1500; // 1.5 detik cooldown antara scan
    
    // Check if required elements exist
    if (!scannerContainer || !scannerVideo || !closeScannerBtn || !barcodeInput) {
        console.error('Required scanner elements not found in DOM');
        return;
    }
    
    // Menambahkan indikator status
    const createStatusIndicator = () => {
        // Remove existing status box if any
        const existingStatus = document.getElementById('scannerStatus');
        if (existingStatus) existingStatus.remove();
        
        const statusBox = document.createElement('div');
        statusBox.id = 'scannerStatus';
        statusBox.style.position = 'absolute';
        statusBox.style.bottom = '10px';
        statusBox.style.left = '10px';
        statusBox.style.right = '10px';
        statusBox.style.backgroundColor = 'rgba(0,0,0,0.7)';
        statusBox.style.color = 'white';
        statusBox.style.padding = '8px';
        statusBox.style.borderRadius = '4px';
        statusBox.style.fontSize = '14px';
        statusBox.style.textAlign = 'center';
        statusBox.textContent = 'Memulai kamera...';
        scannerContainer.appendChild(statusBox);
        return statusBox;
    };
    
    // Membuat overlay untuk menampilkan area scan
    const createScanOverlay = () => {
        // Remove existing overlay if any
        const existingOverlay = document.getElementById('scanOverlay');
        if (existingOverlay) existingOverlay.remove();
        
        const overlay = document.createElement('div');
        overlay.id = 'scanOverlay';
        overlay.style.position = 'absolute';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.border = '2px solid #FF0000';
        overlay.style.boxSizing = 'border-box';
        overlay.style.pointerEvents = 'none';
        
        // Area target di tengah
        const targetArea = document.createElement('div');
        targetArea.style.position = 'absolute';
        targetArea.style.top = '30%';
        targetArea.style.left = '15%';
        targetArea.style.width = '70%';
        targetArea.style.height = '40%';
        targetArea.style.border = '2px dashed #00FF00';
        targetArea.style.boxSizing = 'border-box';
        
        overlay.appendChild(targetArea);
        scannerContainer.appendChild(overlay);
        
        // Apply dark mode settings if needed
        updateScannerForDarkMode(isDarkMode);
        
        return overlay;
    };
    
    // Fungsi untuk memproses barcode yang terdeteksi
    function processDetectedBarcode(barcode) {
        const now = Date.now();
        
        // Mencegah scan ganda dengan cooldown dan cek kode terakhir
        if (lastScannedCode === barcode && (now - lastScanTime) < scanCooldown) {
            return false;
        }
        
        lastScannedCode = barcode;
        lastScanTime = now;
        
        // Update status indikator
        const statusBox = document.getElementById('scannerStatus');
        if (statusBox) {
            statusBox.textContent = `Barcode terdeteksi: ${barcode}`;
            statusBox.style.backgroundColor = 'rgba(0,128,0,0.7)'; // Warna hijau untuk sukses
            
            // Reset status setelah beberapa saat
            setTimeout(() => {
                if (statusBox && statusBox.parentNode) {
                    statusBox.textContent = 'Siap scan berikutnya, kamera tetap aktif...';
                    statusBox.style.backgroundColor = 'rgba(0,0,0,0.7)';
                }
            }, 3000);
        }
        
        // Update barcode input and trigger Livewire action
        try {
            if (window.Livewire && barcodeInput) {
                const wireEl = barcodeInput.closest('[wire\\:id]');
                if (wireEl) {
                    const wireId = wireEl.getAttribute('wire:id');
                    if (wireId && window.Livewire) {
                        // For Livewire 3.x
                        window.Livewire.find(wireId).set('barcode', barcode);
                        window.Livewire.find(wireId).call('addToCart');
                        return true;
                    } else if (wireId && window.livewire) {
                        // For Livewire 2.x
                        window.livewire.find(wireId).set('barcode', barcode);
                        window.livewire.find(wireId).call('addToCart');
                        return true;
                    }
                }
            }
            
            // Fallback to input events if Livewire direct call fails
            if (barcodeInput) {
                barcodeInput.value = barcode;
                barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Trigger enter event for addToCart
                barcodeInput.dispatchEvent(new KeyboardEvent('keydown', { 
                    key: 'Enter', 
                    code: 'Enter', 
                    keyCode: 13, 
                    bubbles: true 
                }));
            }
        } catch (e) {
            console.error("Error dengan Livewire:", e);
        }
        
        return true;
    }
    
    // Simplified camera access with progressive fallbacks
    async function startCamera() {
        // Verify Quagga is loaded
        if (!checkQuaggaLoaded()) {
            return;
        }
        
        const statusBox = createStatusIndicator();
        createScanOverlay();
        
        try {
            // Start with simple constraints for maximum compatibility
            statusBox.textContent = 'Mengakses kamera...';
            
            // Try with simple constraints first
            mediaStream = await navigator.mediaDevices.getUserMedia({ 
                video: true 
            });
            
            scannerVideo.srcObject = mediaStream;
            await scannerVideo.play();
            scannerActive = true;
            
            // Only after camera is working, initialize Quagga
            initializeQuagga(statusBox);
            
        } catch (err) {
            console.error("Gagal mengakses kamera:", err);
            
            if (statusBox) {
                if (err.name === 'NotAllowedError') {
                    statusBox.textContent = 'Akses kamera ditolak. Mohon berikan izin kamera di browser Anda.';
                } else if (err.name === 'NotFoundError') {
                    statusBox.textContent = 'Tidak ada kamera yang ditemukan pada perangkat ini.';
                } else if (err.name === 'NotReadableError') {
                    statusBox.textContent = 'Kamera sedang digunakan oleh aplikasi lain.';
                } else if (err.name === 'OverconstrainedError') {
                    statusBox.textContent = 'Kamera tidak mendukung konfigurasi yang diminta. Mencoba dengan pengaturan dasar...';
                    
                    // Try again with more basic constraints
                    try {
                        mediaStream = await navigator.mediaDevices.getUserMedia({ 
                            video: {
                                facingMode: "environment",
                                width: { ideal: 640 },
                                height: { ideal: 480 }
                            }
                        });
                        
                        scannerVideo.srcObject = mediaStream;
                        await scannerVideo.play();
                        scannerActive = true;
                        
                        // Update status
                        statusBox.textContent = 'Kamera berhasil dimulai dengan pengaturan alternatif...';
                        statusBox.style.backgroundColor = 'rgba(0,0,0,0.7)';
                        
                        // Initialize Quagga
                        initializeQuagga(statusBox);
                    } catch (fallbackErr) {
                        console.error("Fallback camera akses gagal:", fallbackErr);
                        statusBox.textContent = `Gagal mengakses kamera dengan pengaturan alternatif: ${fallbackErr.message}`;
                        statusBox.style.backgroundColor = 'rgba(255,0,0,0.7)';
                    }
                } else {
                    statusBox.textContent = `Gagal mengakses kamera: ${err.message}`;
                    statusBox.style.backgroundColor = 'rgba(255,0,0,0.7)';
                }
            }
        }
    }
    
    // Simplified Quagga initialization
    function initializeQuagga(statusBox) {
        try {
            if (Quagga.canvas) {
                try { Quagga.stop(); } catch(e) { console.log('Quagga belum dimulai'); }
            }
            
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: scannerVideo,
                    constraints: {
                        // Use existing stream, no need to request camera again
                        width: 640,
                        height: 480
                    },
                },
                locator: { 
                    patchSize: "medium", 
                    halfSample: true  // Better performance
                },
                numOfWorkers: 2,  // Lower for better compatibility 
                frequency: 10,
                decoder: {
                    readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "upc_reader"]
                },
                locate: true
            }, function(err) {
                if (err) {
                    console.error("Error initializing scanner:", err);
                    if (statusBox) {
                        statusBox.textContent = `Tidak dapat memulai scanner: ${err}`;
                        statusBox.style.backgroundColor = 'rgba(255,0,0,0.7)';
                    }
                    return;
                }
                
                // Ketika barcode terdeteksi
                Quagga.onDetected(function(result) {
                    const barcode = result.codeResult.code;
                    console.log("Barcode terdeteksi:", barcode);
                    
                    // Proses barcode terdeteksi (tidak hentikan scanner)
                    processDetectedBarcode(barcode);
                });
                
                Quagga.start();
                
                if (statusBox) {
                    statusBox.textContent = 'Siap untuk memindai. Arahkan barcode ke area hijau';
                    statusBox.style.backgroundColor = 'rgba(0,0,0,0.7)';
                }
            });
        } catch (err) {
            console.error("Error initializing Quagga:", err);
            if (statusBox) {
                statusBox.textContent = `Error inisialisasi scanner: ${err.message}`;
                statusBox.style.backgroundColor = 'rgba(255,0,0,0.7)';
            }
        }
    }
    
    // Main function to start the scanner
    function startScanner() {
        // Reset state if already active
        if (scannerActive) {
            stopScanner();
        }
        
        // Begin camera initialization
        startCamera();
    }
    
    // Function to stop the scanner
    function stopScanner() {
        if (Quagga) {
            try { Quagga.stop(); } catch (e) { console.log('Quagga sudah dihentikan'); }
        }
        
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        
        scannerActive = false;
        
        // Remove added elements
        const statusBox = document.getElementById('scannerStatus');
        if (statusBox) statusBox.remove();
        
        const overlay = document.getElementById('scanOverlay');
        if (overlay) overlay.remove();
    }
    
    // Add event listeners
    if (closeScannerBtn) {
        closeScannerBtn.addEventListener('click', function() {
            stopScanner();
            scannerContainer.classList.add('hidden');
        });
    }
    
    // Function to restart scanner when container becomes visible
    const restartScannerIfVisible = () => {
        if (!scannerContainer.classList.contains('hidden') && !scannerActive) {
            startScanner();
        }
    };
    
    // Show scanner container on click (if it was hidden)
    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('#showScanner')) {
            scannerContainer.classList.remove('hidden');
            restartScannerIfVisible();
        }
    });
    
    // Cleanup on page navigation or unload
    document.addEventListener('turbolinks:before-visit', function() {
        if (scannerActive) {
            stopScanner();
        }
    });
    
    window.addEventListener('beforeunload', function() {
        if (scannerActive) {
            stopScanner();
        }
    });
    
    // Start scanner when page loads with a slight delay to ensure DOM is ready
    setTimeout(() => {
        startScanner();
    }, 500);
});
</script>

<!-- Quagga Script - Gunakan CDN yang lebih reliable -->
<script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>

    <!-- Quagga Script -->
   
</x-filament::page>