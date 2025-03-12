<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Contracts\View\View;

class Kasir extends Page
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string $view = 'filament.pages.kasir';
    
    public $barcode = '';
    public array $cart = [];
    public float $total = 0;
    public $products = []; // Define the products variable
    public $search = ''; // Tambahkan properti untuk pencarian
    
    protected $listeners = [
        'refreshCart' => '$refresh'
    ];
    
    public function mount(): void
    {
        $this->cart = Session::get('cart', []);
        $this->calculateTotal();
        
        // Inisialisasi produk dengan stok > 0
        $this->loadProducts();
        
        // Inisialisasi form pencarian
        $this->form->fill();
    }
    
    // Method untuk memuat produk berdasarkan pencarian
    public function loadProducts(): void
    {
        $query = Product::where('stok', '>', 0);
        
        // Filter berdasarkan pencarian jika ada
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }
        
        $this->products = $query->get();
    }
    
    // Method untuk memproses perubahan pada field pencarian
    public function updatedSearch(): void
    {
        $this->loadProducts();
    }
    
    // Form untuk pencarian
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->placeholder('Cari produk...')
                    ->debounce(500)
                    ->live(onBlur: false)
                    ->afterStateUpdated(function () {
                        $this->loadProducts();
                    }),
            ]);
    }
    
    public function addToCart($productId = null)
    {
        // Tambahkan delay kecil untuk menampilkan animasi loading
        usleep(300000); // 300ms delay untuk UX yang lebih baik
        
        // If product ID is provided directly (from product list)
        if ($productId) {
            $produk = Product::find($productId);
        } else {
            // Otherwise use barcode
            $barcode = trim((string) $this->barcode);
            
            if (empty($barcode)) {
                $this->dispatch('scanError', 'Barcode tidak boleh kosong!');
                return;
            }
            
            $produk = Product::where('barcode', $barcode)->first();
        }
        
        if (!$produk) {
            $this->dispatch('scanError', 'Produk tidak ditemukan!');
            return;
        }
        
        if ($produk->stok <= 0) {
            $this->dispatch('scanError', 'Stok produk ini habis!');
            return;
        }
        
        $cart = Session::get('cart', []);
        $productId = $produk->id;
        
        if (isset($cart[$productId])) {
            if ($cart[$productId]['jumlah'] >= $produk->stok) {
                $this->dispatch('scanError', 'Jumlah melebihi stok yang tersedia!');
                return;
            }
            
            $cart[$productId]['jumlah'] += 1;
            $cart[$productId]['subtotal'] = $cart[$productId]['harga'] * $cart[$productId]['jumlah'];
        } else {
            $cart[$productId] = [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'harga' => $produk->harga,
                'jumlah' => 1,
                'subtotal' => $produk->harga
            ];
        }
        
        Session::put('cart', $cart);
        $this->cart = $cart;
        $this->calculateTotal();
        
        // Reset barcode input
        $this->barcode = '';
        
        // Notifikasi ke JavaScript bahwa produk telah ditambahkan
        $this->dispatch('productAdded', 'Produk ditambahkan: ' . $produk->nama);
    }
    
    public function updateQuantity($productId, $quantity)
    {
        $cart = Session::get('cart', []);
        
        // Ensure the product ID exists in the cart
        if (isset($cart[$productId])) {
            $produk = Product::find($productId);
            
            if ($produk && $quantity > $produk->stok) {
                Session::flash('error', 'Jumlah melebihi stok yang tersedia!');
                $cart[$productId]['jumlah'] = $produk->stok;
            } else {
                $cart[$productId]['jumlah'] = max(1, intval($quantity));
            }
            
            $cart[$productId]['subtotal'] = $cart[$productId]['harga'] * $cart[$productId]['jumlah'];
            Session::put('cart', $cart);
            $this->cart = $cart;
            $this->calculateTotal();
        }
    }
    
    public function removeItem($productId)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }
        
        $this->cart = $cart;
        $this->calculateTotal();
    }
    
    public function calculateTotal()
    {
        $this->total = 0;
        
        foreach ($this->cart as $item) {
            $this->total += $item['subtotal'];
        }
    }
    
    public function checkout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang belanja kosong!');
            return;
        }

        DB::beginTransaction();

        try {
            $totalAmount = collect($this->cart)->sum('subtotal');

            $transaction = Transaction::create([
                'total_amount' => $totalAmount,
            ]);

            foreach ($this->cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['nama'],
                    'quantity' => $item['jumlah'],
                    'price' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Update stock
                $product = Product::find($item['id']);
                $product->stok -= $item['jumlah'];
                $product->save();
            }

            DB::commit();

            $this->cart = [];
            session()->flash('message', 'Transaksi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage());
        }

        $transactionId = $transaction->id;
        
        // Clear the cart
        $this->cart = [];
        
        // Use dispatch instead of emit for Livewire 3
        $this->dispatch('checkoutCompleted', transactionId: $transactionId);
    
        session()->flash('message', 'Transaksi berhasil!');
    }
}