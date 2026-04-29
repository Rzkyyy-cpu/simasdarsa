@extends('layouts.app')

@section('title', 'Kasir POS')
@section('page-title', 'Kasir (Point of Sale)')
@section('page-subtitle', 'Input transaksi penjualan — stok dikurangi otomatis dengan metode FEFO')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-5 gap-6" x-data="posSystem()">

    {{-- ============================================================ --}}
    {{-- KOLOM KIRI: Pencarian & Daftar Produk --}}
    {{-- ============================================================ --}}
    <div class="xl:col-span-3 space-y-4">

        {{-- Search Box --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                🔍 Cari Produk (Nama / Barcode)
            </label>
            <div class="relative">
                <input
                    type="text"
                    x-model="searchQuery"
                    @input.debounce.300ms="searchProducts()"
                    @keydown.escape="searchResults = []"
                    placeholder="Ketik nama produk atau scan barcode..."
                    class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent"
                >
                <svg class="absolute left-3 top-3.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                {{-- Hasil Pencarian Dropdown --}}
                <div x-show="searchResults.length > 0" x-cloak
                     class="absolute z-30 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-72 overflow-y-auto">
                    <template x-for="product in searchResults" :key="product.id">
                        <button
                            @click="addToCart(product); searchQuery = ''; searchResults = [];"
                            class="w-full text-left px-4 py-3 hover:bg-brand-50 border-b border-gray-50 last:border-0 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-800" x-text="product.name"></p>
                                <p class="text-xs text-gray-400">
                                    <span x-text="product.category"></span>
                                    &bull; Barcode: <span x-text="product.barcode || '-'"></span>
                                </p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-sm font-bold text-brand-600">
                                    Rp <span x-text="formatRupiah(product.sell_price)"></span>
                                </p>
                                <p class="text-xs" :class="product.total_stock < 5 ? 'text-red-500' : 'text-gray-400'">
                                    Stok: <span x-text="product.total_stock + ' ' + product.unit"></span>
                                </p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Loading indicator --}}
            <div x-show="isSearching" class="mt-2 text-xs text-gray-400 flex items-center gap-1">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Mencari produk...
            </div>
        </div>

        {{-- Info FEFO --}}
        <div class="bg-brand-50 border border-brand-100 rounded-xl px-4 py-3 flex items-start gap-3">
            <svg class="w-5 h-5 text-brand-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-xs text-brand-700">
                <p class="font-semibold">Sistem FEFO Aktif (First Expired First Out)</p>
                <p class="mt-0.5 text-brand-600">Stok yang paling dekat tanggal kedaluwarsanya akan dikurangi terlebih dahulu secara otomatis.</p>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KOLOM KANAN: Keranjang Belanja --}}
    {{-- ============================================================ --}}
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 sticky top-4">

            {{-- Header Keranjang --}}
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    🛒 Keranjang Belanja
                    <span x-show="cart.length > 0"
                          class="bg-brand-500 text-white text-xs px-2 py-0.5 rounded-full"
                          x-text="cart.length + ' item'"></span>
                </h3>
                <button x-show="cart.length > 0" @click="clearCart()"
                        class="text-xs text-red-500 hover:text-red-700 font-medium">Kosongkan</button>
            </div>

            {{-- Item Keranjang --}}
            <div class="max-h-64 overflow-y-auto">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="px-4 py-3 border-b border-gray-50 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate" x-text="item.name"></p>
                            <p class="text-xs text-gray-400">
                                Rp <span x-text="formatRupiah(item.sell_price)"></span>
                                / <span x-text="item.unit"></span>
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="decreaseQty(index)"
                                    class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center text-sm font-bold">
                                −
                            </button>
                            <input type="number"
                                   x-model.number="item.quantity"
                                   @change="updateQty(index, $event.target.value)"
                                   min="1" :max="item.total_stock"
                                   class="w-12 text-center text-sm border border-gray-200 rounded-lg py-1 focus:outline-none focus:ring-1 focus:ring-brand-400">
                            <button @click="increaseQty(index)"
                                    class="w-7 h-7 rounded-lg bg-brand-500 hover:bg-brand-600 text-white flex items-center justify-center text-sm font-bold">
                                +
                            </button>
                        </div>
                        <div class="text-right min-w-[70px]">
                            <p class="text-sm font-semibold text-gray-800">
                                Rp <span x-text="formatRupiah(item.sell_price * item.quantity)"></span>
                            </p>
                            <button @click="removeItem(index)" class="text-xs text-red-400 hover:text-red-600">hapus</button>
                        </div>
                    </div>
                </template>

                {{-- Empty state --}}
                <div x-show="cart.length === 0" class="px-5 py-10 text-center text-sm text-gray-400">
                    <svg class="w-12 h-12 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Keranjang masih kosong.<br>Cari produk di kiri untuk menambahkan.
                </div>
            </div>

            {{-- Total & Pembayaran --}}
            <div class="px-5 py-4 border-t border-gray-100 space-y-3" x-show="cart.length > 0">

                {{-- Subtotal --}}
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-semibold text-gray-800">Rp <span x-text="formatRupiah(totalAmount)"></span></span>
                </div>

                {{-- Input Pembayaran --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 block mb-1">Uang Diterima</label>
                    <input type="number"
                           x-model.number="payment"
                           :min="totalAmount"
                           placeholder="Masukkan nominal pembayaran"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>

                {{-- Kembalian --}}
                <div class="flex justify-between items-center" x-show="payment >= totalAmount">
                    <span class="text-sm text-gray-500">Kembalian</span>
                    <span class="font-bold text-green-600 text-lg">
                        Rp <span x-text="formatRupiah(payment - totalAmount)"></span>
                    </span>
                </div>

                {{-- Nama Kasir --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 block mb-1">Kasir</label>
                    <input type="text" x-model="cashierName" placeholder="Nama kasir"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>

                {{-- Tombol Proses --}}
                <button @click="processSale()"
                        :disabled="cart.length === 0 || payment < totalAmount || isProcessing"
                        :class="cart.length === 0 || payment < totalAmount || isProcessing
                                ? 'bg-gray-300 cursor-not-allowed'
                                : 'bg-brand-500 hover:bg-brand-600 shadow-lg hover:shadow-brand-200'"
                        class="w-full text-white font-semibold py-3 rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                    <template x-if="isProcessing">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <span x-text="isProcessing ? 'Memproses...' : '✅ Proses Transaksi'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Sukses --}}
<div x-show="showSuccess" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center" x-show="showSuccess"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-1">Transaksi Berhasil!</h3>
        <p class="text-sm text-gray-500 mb-4" x-text="'Invoice: ' + lastInvoice"></p>

        <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total Belanja</span>
                <span class="font-semibold" x-text="'Rp ' + formatRupiah(lastTotal)"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Bayar</span>
                <span class="font-semibold" x-text="'Rp ' + formatRupiah(lastPayment)"></span>
            </div>
            <div class="flex justify-between text-sm border-t pt-2">
                <span class="text-gray-700 font-semibold">Kembalian</span>
                <span class="font-bold text-green-600 text-lg" x-text="'Rp ' + formatRupiah(lastChange)"></span>
            </div>
        </div>

        <button @click="showSuccess = false; clearCart();"
                class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3 rounded-xl">
            Transaksi Baru
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posSystem() {
    return {
        searchQuery: '',
        searchResults: [],
        isSearching: false,
        cart: [],
        payment: 0,
        cashierName: 'Kasir',
        isProcessing: false,
        showSuccess: false,
        lastInvoice: '',
        lastTotal: 0,
        lastPayment: 0,
        lastChange: 0,

        get totalAmount() {
            return this.cart.reduce((sum, item) => sum + (item.sell_price * item.quantity), 0);
        },

        formatRupiah(val) {
            return new Intl.NumberFormat('id-ID').format(val || 0);
        },

        async searchProducts() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            this.isSearching = true;
            try {
                const res = await fetch(`/kasir/cari-produk?q=${encodeURIComponent(this.searchQuery)}`);
                this.searchResults = await res.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.isSearching = false;
            }
        },

        addToCart(product) {
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.quantity < product.total_stock) {
                    existing.quantity++;
                    window.notif.info(`Jumlah ${product.name} ditambah menjadi ${existing.quantity}`);
                }
                else {
                    window.notif.error(`Stok ${product.name} tidak cukup!`, 'Stok Habis');
                }
            } else {
                this.cart.push({ ...product, quantity: 1 });
                window.notif.success(`${product.name} ditambahkan ke keranjang`);
            }
            // Auto-set payment jika belum diisi
            if (this.payment < this.totalAmount) {
                this.payment = this.totalAmount;
            }
        },

        increaseQty(index) {
            const item = this.cart[index];
            if (item.quantity < item.total_stock) {
                item.quantity++;
            } else {
                window.notif.warning(`Stok maksimal ${item.name} tercapai`, 'Batas Stok');
            }
        },

        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            } else {
                this.removeItem(index);
            }
        },

        updateQty(index, val) {
            const qty = parseInt(val);
            const item = this.cart[index];
            if (qty < 1) item.quantity = 1;
            else if (qty > item.total_stock) {
                item.quantity = item.total_stock;
                window.notif.warning(`Stok ${item.name} terbatas`, 'Batas Stok');
            } else {
                item.quantity = qty;
            }
        },

        removeItem(index) {
            const name = this.cart[index].name;
            this.cart.splice(index, 1);
            window.notif.info(`${name} dihapus dari keranjang`);
        },

        clearCart() {
            if (this.cart.length > 0 && !this.showSuccess) {
                window.notif.info('Keranjang belanja telah dikosongkan');
            }
            this.cart = [];
            this.payment = 0;
        },

        async processSale() {
            if (this.cart.length === 0 || this.payment < this.totalAmount) return;

            this.isProcessing = true;
            try {
                const payload = {
                    items: this.cart.map(i => ({
                        product_id: i.id,
                        quantity: i.quantity,
                    })),
                    total_payment: this.payment,
                    cashier: this.cashierName,
                };

                const res = await fetch('/kasir/proses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (data.success) {
                    // 1. Munculkan Toast Notification (Pojok kanan bawah)
                    window.notif.success(`Transaksi ${data.data.invoice_number} berhasil!`, 'Sukses');

                    // 2. Simpan data untuk ditampilkan di modal detail
                    this.lastInvoice  = data.data.invoice_number;
                    this.lastTotal    = data.data.total_amount;
                    this.lastPayment  = data.data.total_payment;
                    this.lastChange   = data.data.change_amount;
                    
                    // 3. Munculkan modal sukses (Tengah layar)
                    this.showSuccess  = true;
                    
                    // 4. Bersihkan keranjang belanja setelah modal muncul
                    this.$nextTick(() => {
                        this.cart = [];
                        this.payment = 0;
                        this.searchQuery = '';
                        this.searchResults = [];
                    });
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (e) {
                alert('Error jaringan: ' + e.message);
            } finally {
                this.isProcessing = false;
            }
        },
    }
}
</script>
@endpush