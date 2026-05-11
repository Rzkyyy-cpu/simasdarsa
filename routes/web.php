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
    // PIMPINAN ROUTES (Now accessible to all, limited by permission)
    // -----------------------------------------------------------------------
    Route::prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/laporan-eksekutif', [ReportController::class, 'executiveReport'])->name('executive-report')->middleware('permission:menus.laporan.eksekutif');
        Route::get('/statistik-stok-kritis', [ReportController::class, 'executiveReport'])->name('critical-stock-stats')->middleware('permission:menus.laporan.eksekutif');
        Route::get('/monitoring-expired-detail', [StockBatchController::class, 'expiryMonitorDetail'])->name('expiry-monitor-detail')->middleware('permission:menus.stok.expiry-monitor');
    });

    // -----------------------------------------------------------------------
    // TIM IT ROUTES (Now accessible to all, limited by permission)
    // -----------------------------------------------------------------------
    Route::prefix('tim-it')->name('tim-it.')->group(function () {
        Route::get('/user-management', [AdminController::class, 'userManagement'])->name('user-management')->middleware('permission:menus.tim-it.user-management');
        Route::post('/user-management', [AdminController::class, 'storeUser'])->name('user-management.store')->middleware('permission:menus.tim-it.user-management');
        Route::put('/user-management/{user}', [AdminController::class, 'updateUser'])->name('user-management.update')->middleware('permission:menus.tim-it.user-management');
        Route::delete('/user-management/{user}', [AdminController::class, 'deleteUser'])->name('user-management.destroy')->middleware('permission:menus.tim-it.user-management');
        Route::patch('/user-management/{user}/permissions', [AdminController::class, 'updatePermissions'])->name('user-management.update-permissions')->middleware('permission:menus.tim-it.user-management');
        Route::get('/user-management/{user}/details', [AdminController::class, 'userDetails'])->name('user-management.details')->middleware('permission:menus.tim-it.user-management');
        Route::put('/user-management/{user}/details', [AdminController::class, 'updateUserDetails'])->name('user-management.update-details')->middleware('permission:menus.tim-it.user-management');
        
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs')->middleware('permission:menus.tim-it.audit-logs');
        Route::get('/audit-logs/export', [AdminController::class, 'exportAuditLogs'])->name('audit-logs.export')->middleware('permission:menus.tim-it.audit-logs');
    });

    // -----------------------------------------------------------------------
    // MANAGER ROUTES (Now accessible to all, limited by permission)
    // -----------------------------------------------------------------------
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/monitoring-expired', [StockBatchController::class, 'expiryMonitor'])->name('expiry-monitor')->middleware('permission:menus.stok.expiry-monitor');
        Route::get('/monitoring-expired-detail', [StockBatchController::class, 'expiryMonitorDetail'])->name('expiry-monitor-detail')->middleware('permission:menus.stok.expiry-monitor');
        Route::get('/produk-management', [ProductController::class, 'management'])->name('product-management')->middleware('permission:menus.produk.index');
        Route::get('/verifikasi-stok-masuk', [StockBatchController::class, 'verifyIncomingStock'])->name('verify-incoming-stock')->middleware('permission:menus.manager.verify-incoming-stock');
        Route::post('/verifikasi-stok-masuk/{batch}', [StockBatchController::class, 'verifyBatch'])->name('verify-batch')->middleware('permission:menus.manager.verify-incoming-stock');
        Route::get('/status-lokasi-barang', [StockBatchController::class, 'itemStatusLocation'])->name('item-status-location')->middleware('permission:menus.manager.item-status-location');
    });

    // -----------------------------------------------------------------------
    // KASIR ROUTES (Now accessible to all, limited by permission)
    // -----------------------------------------------------------------------
    Route::prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index')->middleware('permission:menus.kasir.index'); 
        Route::post('/proses', [SaleController::class, 'processSale'])->name('proses')->middleware('permission:menus.kasir.index');
        Route::get('/riwayat-penjualan', [SaleController::class, 'history'])->name('sale-history')->middleware('permission:menus.penjualan.index'); 
        Route::get('/cari-produk', [SaleController::class, 'searchProduct'])->name('search-product')->middleware('permission:menus.kasir.index');
        Route::get('/monitoring-expired', [StockBatchController::class, 'expiryMonitor'])->name('expiry-monitor')->middleware('permission:menus.stok.expiry-monitor');
        Route::get('/monitoring-expired-detail', [StockBatchController::class, 'expiryMonitorDetail'])->name('expiry-monitor-detail')->middleware('permission:menus.stok.expiry-monitor');
        Route::get('/update-stok-fisik', [StockBatchController::class, 'updatePhysicalStock'])->name('update-physical-stock')->middleware('permission:menus.kasir.update-physical-stock');
        Route::post('/update-stok-fisik', [StockBatchController::class, 'savePhysicalStockUpdate'])->name('save-physical-stock-update')->middleware('permission:menus.kasir.update-physical-stock');
        Route::get('/status-lokasi-barang', [StockBatchController::class, 'itemStatusLocation'])->name('item-status-location')->middleware('permission:menus.manager.item-status-location');
    });

    // -----------------------------------------------------------------------
    // SHARED ROUTES (Limited by permission)
    // -----------------------------------------------------------------------

    // MANAJEMEN PRODUK
    Route::group([], function () {
        Route::get('produk', [ProductController::class, 'index'])->name('produk.index')->middleware('permission:menus.produk.index');
        Route::get('produk/create', [ProductController::class, 'create'])->name('produk.create')->middleware('permission:crud.create');
        Route::post('produk', [ProductController::class, 'store'])->name('produk.store')->middleware('permission:crud.create');
        Route::get('produk/{produk}', [ProductController::class, 'show'])->name('produk.show')->middleware('permission:crud.read');
        Route::get('produk/{produk}/edit', [ProductController::class, 'edit'])->name('produk.edit')->middleware('permission:crud.update');
        Route::put('produk/{produk}', [ProductController::class, 'update'])->name('produk.update')->middleware('permission:crud.update');
        Route::delete('produk/{produk}', [ProductController::class, 'destroy'])->name('produk.destroy')->middleware('permission:crud.delete');
    });

    // MANAJEMEN BATCH STOK
    Route::prefix('stok')->name('stok.')->group(function () {
        Route::get('/monitoring-expired', [StockBatchController::class, 'expiryMonitor'])->name('expiry-monitor')->middleware('permission:menus.stok.expiry-monitor');
        Route::get('/', [StockBatchController::class, 'index'])->name('index')->middleware('permission:menus.stok.index');
        Route::get('/tambah', [StockBatchController::class, 'create'])->name('create')->middleware('permission:crud.create');
        Route::post('/', [StockBatchController::class, 'store'])->name('store')->middleware('permission:crud.create');
        Route::get('/{batch}/edit', [StockBatchController::class, 'edit'])->name('edit')->middleware('permission:crud.update');
        Route::put('/{batch}', [StockBatchController::class, 'update'])->name('update')->middleware('permission:crud.update');
        Route::delete('/{batch}', [StockBatchController::class, 'destroy'])->name('destroy')->middleware('permission:crud.delete');
    });

    // KASIR (POS)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index')->middleware('permission:menus.kasir.index');
        Route::post('/proses', [SaleController::class, 'processSale'])->name('process')->middleware('permission:menus.kasir.index');
        Route::get('/cari-produk', [SaleController::class, 'searchProduct'])->name('search-product')->middleware('permission:menus.kasir.index');
    });

    // RIWAYAT PENJUALAN
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [SaleController::class, 'history'])->name('index')->middleware('permission:menus.penjualan.index');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show')->middleware('permission:menus.penjualan.index');
    });

   
    // LAPORAN
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/laba-rugi', [SaleController::class, 'profitReport'])->name('laba-rugi')->middleware('permission:menus.laporan.laba-rugi');
        Route::get('/eksekutif', [ReportController::class, 'executiveReport'])->name('eksekutif')->middleware('permission:menus.laporan.eksekutif');
    });

});