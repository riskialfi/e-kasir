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

        // Enhance checkout button functionality
        const checkoutButton = document.getElementById('checkoutButton');
        if (checkoutButton) {
            // Add a click event listener to show loading animation
            checkoutButton.addEventListener('click', function() {
                // The actual checkout is handled by Livewire wire:click
                // This is just for additional UI feedback
                const loadingSpinner = document.getElementById('loadingSpinner');
                const loadingText = document.getElementById('loadingText');

                if (loadingSpinner && loadingText) {
                    loadingText.textContent = 'Memproses checkout...';
                }
            });
        }


        // Listen for checkout error event
        window.Livewire.on('checkoutError', function(errorMessage) {
            // Show error message
            const loadingSpinner = document.getElementById('loadingSpinner');
            const successCheckmark = document.getElementById('successCheckmark');
            const loadingText = document.getElementById('loadingText');
            const successText = document.getElementById('successText');

            if (loadingSpinner && successCheckmark && loadingText && successText) {
                // Hide loading spinner
                loadingSpinner.classList.add('hidden');

                // Show error icon (you could add an error icon element to your HTML)
                // For now, we'll just use the success text area to show the error
                loadingText.classList.add('hidden');
                successText.classList.remove('hidden');
                successText.textContent = errorMessage || 'Terjadi kesalahan saat checkout';
                successText.style.color = 'red';

                // Reset back to loading state after delay
                setTimeout(() => {
                    loadingSpinner.classList.remove('hidden');
                    loadingText.classList.remove('hidden');
                    successText.classList.add('hidden');
                    loadingText.textContent = 'Memproses...';
                    successText.style.color = ''; // Reset color
                }, 3000);
            }
        });

        // For Livewire 3
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('checkoutCompleted', (data) => {
                // Extract the transaction ID from the data object
                const transactionId = data.transactionId;

                console.log('Transaction ID received:', transactionId); // Add this for debugging

                // Show success animation first
                showLoadingSuccess();

                // Update success text to indicate receipt printing
                const successText = document.getElementById('successText');
                if (successText) {
                    successText.textContent = 'Transaksi berhasil! Mencetak struk...';
                }

                // Then open receipt in new tab, make sure transactionId is properly coerced to a string
                setTimeout(() => {
                    window.open('/receipt/print/' + String(transactionId), '_blank', 'width=800,height=600');
                }, 1000);
            });
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
                try { Quagga.stop(); } catch (e) { console.log('Quagga belum dimulai'); }
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
                    halfSample: true // Better performance
                },
                numOfWorkers: 2, // Lower for better compatibility 
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

    setTimeout(() => {
        startScanner();
    }, 500);
});