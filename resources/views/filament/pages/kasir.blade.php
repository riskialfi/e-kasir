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
    </div>

    <!-- Quagga Script - Load before our custom script -->
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    
    <!-- Our custom scanner script -->
    <script src="{{ asset('js/scanner.js') }}"></script>
</x-filament::page>