<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Menguji login, hak akses per permission, dan memastikan semua halaman utama bisa dibuka.
 */
class AccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $menus = []): User
    {
        return User::create([
            'name'        => ucfirst($role),
            'email'       => "{$role}@test.com",
            'password'    => 'password',
            'roles'       => [$role],
            'permissions' => ['crud' => ['read' => true], 'menus' => $menus],
        ]);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_login_berhasil_dengan_role_yang_dimiliki(): void
    {
        $this->user('manager');

        $this->post(route('login.authenticate'), [
            'email'    => 'manager@test.com',
            'password' => 'password',
            'sub_role' => 'manager',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_gagal_jika_password_salah(): void
    {
        $this->user('manager');

        $this->post(route('login.authenticate'), [
            'email'    => 'manager@test.com',
            'password' => 'salah',
            'sub_role' => 'manager',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_login_gagal_jika_memilih_role_yang_tidak_dimiliki(): void
    {
        $this->user('kasir');

        $this->post(route('login.authenticate'), [
            'email'    => 'kasir@test.com',
            'password' => 'password',
            'sub_role' => 'pimpinan',
        ])->assertSessionHasErrors('sub_role');

        $this->assertGuest();
    }

    public function test_kasir_tanpa_izin_tidak_bisa_membuka_laporan(): void
    {
        $kasir = $this->user('kasir', ['kasir.index']);

        $this->actingAs($kasir)
            ->get(route('laporan.laba-rugi'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_kasir_dengan_izin_bisa_membuka_halaman_kasir(): void
    {
        $kasir = $this->user('kasir', ['kasir.index']);

        $this->actingAs($kasir)->get(route('kasir.index'))->assertOk();
    }

    public static function halamanUtama(): array
    {
        return [
            'dashboard'          => ['dashboard'],
            'produk'             => ['produk.index'],
            'tambah produk'      => ['produk.create'],
            'batch stok'         => ['stok.index'],
            'tambah batch'       => ['stok.create'],
            'monitoring expired' => ['stok.expiry-monitor'],
            'kasir'              => ['pos.index'],
            'riwayat penjualan'  => ['penjualan.index'],
            'laba rugi'          => ['laporan.laba-rugi'],
            'laporan eksekutif'  => ['laporan.eksekutif'],
            'verifikasi stok'    => ['manager.verify-incoming-stock'],
            'lokasi barang'      => ['manager.item-status-location'],
            'update stok fisik'  => ['kasir.update-physical-stock'],
            'user management'    => ['tim-it.user-management'],
            'audit log'          => ['tim-it.audit-logs'],
        ];
    }

    #[DataProvider('halamanUtama')]
    public function test_halaman_utama_bisa_dibuka_pimpinan(string $routeName): void
    {
        // Siapkan sedikit data supaya halaman tidak kosong
        $product = Product::create([
            'barcode' => '8000000000001', 'name' => 'Aqua 600ml', 'category' => 'Minuman',
            'min_stock' => 10, 'unit' => 'botol',
        ]);
        StockBatch::create([
            'product_id' => $product->id, 'batch_code' => 'BTH-1', 'buy_price' => 3000,
            'sell_price' => 4000, 'initial_quantity' => 50, 'current_quantity' => 50,
            'expired_date' => now()->addDays(5)->toDateString(), 'received_date' => now()->toDateString(),
        ]);

        $pimpinan = $this->user('pimpinan');
        $this->actingAs($pimpinan)->postJson(route('kasir.proses'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'total_payment' => 10000,
        ])->assertOk();

        $this->actingAs($pimpinan)->get(route($routeName))->assertOk();
    }

    public function test_halaman_detail_bisa_dibuka_pimpinan(): void
    {
        $product = Product::create([
            'barcode' => '8000000000001', 'name' => 'Aqua 600ml', 'category' => 'Minuman',
            'min_stock' => 10, 'unit' => 'botol',
        ]);
        $batch = StockBatch::create([
            'product_id' => $product->id, 'batch_code' => 'BTH-1', 'buy_price' => 3000,
            'sell_price' => 4000, 'initial_quantity' => 50, 'current_quantity' => 50,
            'expired_date' => now()->addDays(5)->toDateString(), 'received_date' => now()->toDateString(),
        ]);
        $pimpinan = $this->user('pimpinan');
        $this->actingAs($pimpinan)->postJson(route('kasir.proses'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'total_payment' => 10000,
        ])->assertOk();
        $sale = \App\Models\Sale::first();

        $this->get(route('produk.show', $product))->assertOk();
        $this->get(route('produk.edit', $product))->assertOk();
        $this->get(route('stok.edit', $batch))->assertOk();
        $this->get(route('penjualan.show', $sale))->assertOk();
        $this->get(route('tim-it.user-management.details', $pimpinan))->assertOk();
    }
}
