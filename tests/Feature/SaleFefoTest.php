<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji logika FEFO (First Expired, First Out) di proses penjualan kasir.
 */
class SaleFefoTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::create([
            'name'        => 'Kasir Test',
            'email'       => 'kasir@test.com',
            'password'    => 'password',
            'roles'       => ['kasir'],
            'permissions' => ['crud' => ['read' => true], 'menus' => ['kasir.index']],
        ]);

        $this->product = Product::create([
            'barcode'   => '8000000000001',
            'name'      => 'Indomie Goreng',
            'category'  => 'Mie & Bubur',
            'min_stock' => 10,
            'unit'      => 'pcs',
        ]);
    }

    /** Helper: buat batch stok dengan tanggal expired tertentu. */
    private function batch(string $code, int $qty, string $expiredDate): StockBatch
    {
        return StockBatch::create([
            'product_id'       => $this->product->id,
            'batch_code'       => $code,
            'buy_price'        => 2800,
            'sell_price'       => 4500,
            'initial_quantity' => $qty,
            'current_quantity' => $qty,
            'expired_date'     => $expiredDate,
            'received_date'    => now()->toDateString(),
        ]);
    }

    /** Helper: kirim transaksi ke endpoint kasir. */
    private function sell(int $qty, int $payment = 1_000_000)
    {
        return $this->actingAs($this->kasir)->postJson(route('kasir.proses'), [
            'items'         => [['product_id' => $this->product->id, 'quantity' => $qty]],
            'total_payment' => $payment,
            'cashier'       => 'Kasir Test',
        ]);
    }

    public function test_stok_diambil_dari_batch_yang_paling_cepat_expired(): void
    {
        // Batch dibuat dengan urutan acak, bukan urutan expired
        $lama   = $this->batch('BTH-LAMA', 10, now()->addMonths(6)->toDateString());
        $dekat  = $this->batch('BTH-DEKAT', 10, now()->addDays(10)->toDateString());

        $this->sell(4)->assertOk()->assertJson(['success' => true]);

        // Yang berkurang harus batch yang expired-nya paling dekat
        $this->assertSame(6, $dekat->fresh()->current_quantity);
        $this->assertSame(10, $lama->fresh()->current_quantity);
    }

    public function test_kebutuhan_melebihi_satu_batch_diambil_dari_batch_berikutnya(): void
    {
        $dekat = $this->batch('BTH-1', 3, now()->addDays(5)->toDateString());
        $lama  = $this->batch('BTH-2', 10, now()->addDays(60)->toDateString());

        $this->sell(5)->assertOk();

        $this->assertSame(0, $dekat->fresh()->current_quantity);
        $this->assertSame(8, $lama->fresh()->current_quantity);

        // Tercatat dua baris detail penjualan, satu per batch
        $this->assertDatabaseHas('sale_details', ['batch_id' => $dekat->id, 'quantity' => 3]);
        $this->assertDatabaseHas('sale_details', ['batch_id' => $lama->id, 'quantity' => 2]);
    }

    public function test_batch_yang_sudah_expired_tidak_ikut_dijual(): void
    {
        $expired = $this->batch('BTH-EXP', 10, now()->subDay()->toDateString());
        $aman    = $this->batch('BTH-OK', 10, now()->addDays(30)->toDateString());

        $this->sell(2)->assertOk();

        $this->assertSame(10, $expired->fresh()->current_quantity);
        $this->assertSame(8, $aman->fresh()->current_quantity);
    }

    public function test_stok_tidak_cukup_ditolak_dan_stok_tidak_berubah(): void
    {
        $batch = $this->batch('BTH-1', 3, now()->addDays(30)->toDateString());

        $this->sell(5)
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Stok Indomie Goreng tidak cukup. Dibutuhkan: 5, Tersedia: 3']);

        $this->assertSame(3, $batch->fresh()->current_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_pembayaran_kurang_ditolak_dan_stok_tidak_berubah(): void
    {
        $batch = $this->batch('BTH-1', 10, now()->addDays(30)->toDateString());

        // 2 x 4.500 = 9.000, tapi hanya bayar 5.000
        $this->sell(2, 5000)->assertStatus(422)->assertJson(['success' => false]);

        $this->assertSame(10, $batch->fresh()->current_quantity);
        $this->assertDatabaseCount('sales', 0);
    }
}
