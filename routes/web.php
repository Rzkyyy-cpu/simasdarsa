<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockBatchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Models\Sale;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIMASDARSA - Route Definitions
|--------------------------------------------------------------------------
*/

// Splash screen
Route::get('/splash', fn() => view('splash'))->name('splash');

// Redirect halaman awal ke splash
Route::get('/', fn() => redirect()->route('splash'));

// -----------------------------------------------------------------------
// AUTHENTICATION
// -----------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// -----------------------------------------------------------------------
// AUTHENTICATED ROUTES
// -----------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // -----------------------------------------------------------------------
    // DASHBOARD (All roles)
    // -----------------------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // -----------------------------------------------------------------------
    // PIMPINAN ROUTES
    // -----------------------------------------------------------------------
    Route::middleware('role:pimpinan')->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/laporan-eksekutif', [ReportController::class, 'executiveReport'])->name('executive-report');
        Route::get('/statistik-stok-kritis', [ReportController::class, 'criticalStockStats'])->name('critical-stock-stats');
        Route::get('/monitoring-expired-detail', [StockBatchController::class, 'expiryMonitorDetail'])->name('expiry-monitor-detail');
        Route::get('/pengaturan-harga', [AdminController::class, 'priceSettings'])->name('price-settings');
        Route::post('/pengaturan-harga', [AdminController::class, 'updatePriceSettings'])->name('update-price-settings');
        Route::get('/pengaturan-diskon', [AdminController::class, 'discountSettings'])->name('discount-settings');
        Route::post('/pengaturan-diskon', [AdminController::class, 'updateDiscountSettings'])->name('update-discount-settings');
    });

    // -----------------------------------------------------------------------
    // TIM IT ROUTES
    // -----------------------------------------------------------------------
    Route::middleware('role:tim_it')->prefix('tim-it')->name('tim-it.')->group(function () {
        Route::get('/user-management', [AdminController::class, 'userManagement'])->name('user-management');
        Route::get('/system-maintenance', [AdminController::class, 'systemMaintenance'])->name('system-maintenance');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/system-testing', [AdminController::class, 'systemTesting'])->name('system-testing');
    });

    // -----------------------------------------------------------------------
    // MANAGER ROUTES
    // -----------------------------------------------------------------------
    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/monitoring-expired', [StockBatchController::class, 'expiryMonitor'])->name('expiry-monitor');
        Route::get('/monitoring-expired-detail', [StockBatchController::class, 'expiryMonitorDetail'])->name('expiry-monitor-detail');
        Route::get('/produk-management', [ProductController::class, 'management'])->name('product-management');
        Route::get('/verifikasi-stok-masuk', [StockBatchController::class, 'verifyIncomingStock'])->name('verify-incoming-stock');
        Route::get('/pengaturan-harga', [AdminController::class, 'priceSettings'])->name('price-settings');
        Route::post('/pengaturan-harga', [AdminController::class, 'updatePriceSettings'])->name('update-price-settings');
        Route::get('/pengaturan-diskon', [AdminController::class, 'discountSettings'])->name('discount-settings');
        Route::post('/pengaturan-diskon', [AdminController::class, 'updateDiscountSettings'])->name('update-discount-settings');
        Route::get('/status-lokasi-barang', [StockBatchController::class, 'itemStatusLocation'])->name('item-status-location');
    });

    // -----------------------------------------------------------------------
    // KASIR ROUTES
    // -----------------------------------------------------------------------
    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index'); 
        Route::post('/proses', [SaleController::class, 'processSale'])->name('proses');
        Route::get('/riwayat-penjualan', [SaleController::class, 'history'])->name('sale-history'); 
        Route::get('/cari-produk', [SaleController::class, 'searchProduct'])->name('search-product');
        Route::get('/monitoring-expired', [StockBatchController::class, 'expiryMonitor'])->name('expiry-monitor');
        Route::get('/monitoring-expired-detail', [StockBatchController::class, 'expiryMonitorDetail'])->name('expiry-monitor-detail');
        Route::get('/update-stok-fisik', [StockBatchController::class, 'updatePhysicalStock'])->name('update-physical-stock');
        Route::post('/update-stok-fisik', [StockBatchController::class, 'savePhysicalStockUpdate'])->name('save-physical-stock-update');
        Route::get('/status-lokasi-barang', [StockBatchController::class, 'itemStatusLocation'])->name('item-status-location');
    });

    // -----------------------------------------------------------------------
    // SHARED ROUTES (Multiple roles can access)
    // -----------------------------------------------------------------------

    // MANAJEMEN PRODUK (Manager & Tim IT)
    Route::middleware('role:manager,tim_it')->group(function () {
        Route::resource('produk', ProductController::class);
    });

    // MANAJEMEN BATCH STOK (Manager & Kasir)
    Route::middleware('role:manager,kasir')->prefix('stok')->name('stok.')->group(function () {
        Route::get('/monitoring-expired', [StockBatchController::class, 'expiryMonitor'])->name('expiry-monitor');
        Route::get('/', [StockBatchController::class, 'index'])->name('index');
        Route::get('/tambah', [StockBatchController::class, 'create'])->name('create');
        Route::post('/', [StockBatchController::class, 'store'])->name('store');
        Route::get('/{batch}/edit', [StockBatchController::class, 'edit'])->name('edit');
        Route::put('/{batch}', [StockBatchController::class, 'update'])->name('update');
        Route::delete('/{batch}', [StockBatchController::class, 'destroy'])->name('destroy');
    });

    // KASIR (POS) - Kasir only
    Route::middleware('role:kasir')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::post('/proses', [SaleController::class, 'processSale'])->name('process');
        Route::get('/cari-produk', [SaleController::class, 'searchProduct'])->name('search-product');
    });

    // RIWAYAT PENJUALAN (Manager & Kasir)
    Route::middleware('role:manager,kasir')->prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [SaleController::class, 'history'])->name('index');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
    });

    // LAPORAN (Pimpinan & Manager)
    Route::middleware('role:pimpinan,manager')->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/laba-rugi', [SaleController::class, 'profitReport'])->name('laba-rugi');
    });

});