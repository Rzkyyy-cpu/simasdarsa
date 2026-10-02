<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_eksekutif_menghitung_pendapatan_dan_laba_kotor(): void
    {
        $pimpinan = User::create([
            'name' => 'Pimpinan', 'email' => 'pimpinan@test.com', 'password' => 'password',
            'roles' => ['pimpinan'], 'permissions' => [],
        ]);
        $product = Product::create([
            'barcode' => '8000000000001', 'name' => 'Aqua 600ml', 'category' => 'Minuman',
            'min_stock' => 10, 'unit' => 'botol',
        ]);
        StockBatch::create([
            'product_id' => $product->id, 'batch_code' => 'BTH-1', 'buy_price' => 3000,
            'sell_price' => 4000, 'initial_quantity' => 50, 'current_quantity' => 50,
            'expired_date' => now()->addMonths(3)->toDateString(), 'received_date' => now()->toDateString(),
        ]);

        // Jual 5 botol: pendapatan 5 x 4.000 = 20.000, laba (4.000 - 3.000) x 5 = 5.000
        $this->actingAs($pimpinan)->postJson(route('kasir.proses'), [
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
            'total_payment' => 20000,
        ])->assertOk();

        $response = $this->get(route('laporan.eksekutif'))->assertOk();
        $summary  = $response->viewData('summary');

        $this->assertEquals(1, $summary->total_transactions);
        $this->assertEquals(20000, $summary->total_revenue);
        $this->assertEquals(5000, $summary->total_profit);
    }
}
