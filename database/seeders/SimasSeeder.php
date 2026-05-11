<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * SimasSeeder - Generate 500 produk dummy khas warung/toko UMKM
 * beserta batch stok dan riwayat penjualan untuk testing.
 *
 * Jalankan dengan: php artisan db:seed --class=SimasSeeder
 */
class SimasSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🛒 SIMASDARSA - Memulai seeding data dummy...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SaleDetail::truncate();
        Sale::truncate();
        StockBatch::truncate();
        Product::truncate();
        \App\Models\User::truncate();

        // Reset auto increment counters
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE stock_batches AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE sales AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE sale_details AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // -------------------------------------------------------
        // DATA PRODUK WARUNG/UMKM (500 produk)
        // -------------------------------------------------------
        $categories = [
            'Minuman'          => $this->getMinuman(),
            'Makanan Ringan'   => $this->getMakananRingan(),
            'Mie & Bubur'      => $this->getMieBubur(),
            'Bumbu & Rempah'   => $this->getBumbuRempah(),
            'Susu & Dairy'     => $this->getSusuDairy(),
            'Rokok & Tembakau' => $this->getRokok(),
            'Alat Tulis'       => $this->getAlatTulis(),
            'Sabun & Kebersihan' => $this->getSabunKebersihan(),
            'Kecantikan'       => $this->getKecantikan(),
            'Frozen Food'      => $this->getFrozenFood(),
        ];

        $allProducts = [];
        $barcode     = 8001000001;

        foreach ($categories as $category => $items) {
            foreach ($items as $item) {
                $allProducts[] = array_merge($item, [
                    'category' => $category,
                    'barcode'  => (string) $barcode++,
                ]);
            }
        }

        // Pastikan tepat 500 produk dengan mengulang jika kurang
        while (count($allProducts) < 500) {
            $template    = $allProducts[array_rand($allProducts)];
            $template['name'] .= ' Varian ' . rand(2, 9);
            $template['barcode'] = (string) $barcode++;
            $allProducts[] = $template;
        }

        $allProducts = array_slice($allProducts, 0, 500);

        $this->command->info('📦 Membuat 500 produk...');
        $bar = $this->command->getOutput()->createProgressBar(count($allProducts));

        $products = [];
        $expiredCount = 0; // Counter for expired products

        foreach ($allProducts as $index => $data) {
            $product = Product::create([
                'barcode'   => $data['barcode'],
                'name'      => $data['name'],
                'category'  => $data['category'],
                'min_stock' => $data['min_stock'],
                'unit'      => $data['unit'],
            ]);

            // Setiap produk punya 2–4 batch stok
            $numBatches = rand(2, 4);
            $currentTotal = 0;

            for ($b = 0; $b < $numBatches; $b++) {
                // Default: Tanggal expired jauh di masa depan (Tahun 2027 - 2028), minimal bulan Agustus
                $expiredDate = Carbon::create(2027, rand(8, 12), rand(1, 28))->addYears(rand(0, 1))->toDateString();

                // Jadikan 3 produk pertama kadaluarsa
                if ($expiredCount < 3 && $b == 0) {
                    $expiredDate = now()->subDays(rand(1, 60))->toDateString();
                }

                $buyPrice  = $data['buy_price'] * (1 + (rand(-5, 5) / 100));
                $sellPrice = $data['sell_price'] * (1 + (rand(-3, 3) / 100));

                // Pastikan stok melimpah
                $qty = rand($product->min_stock, $product->min_stock * 3);
                $currentTotal += $qty;

                StockBatch::create([
                    'product_id'       => $product->id,
                    'batch_code'       => 'BTH-' . strtoupper(substr(md5($product->id . $b), 0, 6)),
                    'buy_price'        => round($buyPrice, -1),
                    'sell_price'       => round($sellPrice, -1),
                    'initial_quantity' => $qty + rand(100, 200),
                    'current_quantity' => $qty,
                    'expired_date'     => $expiredDate,
                    'received_date'    => now()->subDays(rand(1, 90))->toDateString(),
                ]);
            }

            if ($expiredCount < 3) {
                $expiredCount++;
            }

            $products[] = $product;
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();

        // -------------------------------------------------------
        // GENERATE RIWAYAT PENJUALAN (90 hari terakhir)
        // -------------------------------------------------------
        $this->command->info('💳 Membuat riwayat penjualan 90 hari terakhir...');

        $totalTransaksi = 0;

        for ($day = 90; $day >= 0; $day--) {
            $date           = now()->subDays($day);
            $transPerDay    = rand(5, 25); // 5-25 transaksi per hari

            for ($t = 0; $t < $transPerDay; $t++) {
                $numItems    = rand(1, 6);
                $sampleProds = collect($products)->random($numItems);

                $totalAmount = 0;
                $details     = [];

                foreach ($sampleProds as $product) {
                    $batch = StockBatch::where('product_id', $product->id)
                                       ->where('current_quantity', '>', 0)
                                       ->orderBy('expired_date')
                                       ->first();
                    if (!$batch) continue;

                    $qty          = rand(1, min(5, $batch->current_quantity));
                    $subtotal     = $batch->sell_price * $qty;
                    $totalAmount += $subtotal;

                    $details[] = [
                        'batch_id'          => $batch->id,
                        'product_id'        => $product->id,
                        'quantity'          => $qty,
                        'price_at_sale'     => $batch->sell_price,
                        'buy_price_at_sale' => $batch->buy_price,
                        'subtotal'          => $subtotal,
                    ];
                }

                if (empty($details)) continue;

                $payment = $totalAmount + rand(0, 5) * 1000; // Bayar lebih (kembalian)

                $sale = Sale::create([
                    'invoice_number' => 'INV-' . $date->format('Ymd') . '-' . str_pad($totalTransaksi + 1, 6, '0', STR_PAD_LEFT),
                    'sale_date'      => $date->copy()->setTime(rand(7, 21), rand(0, 59)), // Use copy() to avoid modifying original
                    'total_amount'   => $totalAmount,
                    'total_payment'  => $payment,
                    'change_amount'  => $payment - $totalAmount,
                    'cashier'        => collect(['Budi', 'Siti', 'Ahmad', 'Dewi', 'Roni'])->random(),
                ]);

                foreach ($details as $detail) {
                    SaleDetail::create(array_merge($detail, ['sale_id' => $sale->id]));
                }

                $totalTransaksi++;
            }
        }

        $this->command->info("✅ Selesai! Total transaksi: {$totalTransaksi}");

        // -------------------------------------------------------
        // SEEDING USERS DENGAN ROLES
        // -------------------------------------------------------
        $this->command->info('👥 Membuat users dengan roles...');

        $users = [
            [
                'name' => 'Pimpinan Utama',
                'email' => 'pimpinan@simasdarsa.com',
                'password' => bcrypt('password'),
                'roles' => ['pimpinan'],
            ],
            [
                'name' => 'Tim IT Lead',
                'email' => 'tim_it@simasdarsa.com',
                'password' => bcrypt('password'),
                'roles' => ['tim_it'],
            ],
            [
                'name' => 'Manager Toko',
                'email' => 'manager@simasdarsa.com',
                'password' => bcrypt('password'),
                'roles' => ['manager'],
            ],
            [
                'name' => 'Kasir',
                'email' => 'kasir@simasdarsa.com',
                'password' => bcrypt('password'),
                'roles' => ['kasir'],
            ],
        ];

        foreach ($users as $userData) {
            \App\Models\User::create($userData);
        }

        $this->command->info('🚀 Aplikasi siap digunakan untuk testing!');
    }

    // =========================================================
    // DATA PRODUK PER KATEGORI
    // =========================================================

    private function getMinuman(): array
    {
        $produk = [
            ['Aqua Botol 600ml', 3000, 4000, 'botol'], ['Aqua Galon 19L', 16000, 20000, 'galon'],
            ['Teh Botol Sosro 350ml', 4000, 6000, 'botol'], ['Teh Botol Sosro 450ml', 5000, 7000, 'botol'],
            ['Pocari Sweat 350ml', 6000, 8500, 'botol'], ['Pocari Sweat 500ml', 9000, 12000, 'botol'],
            ['Mizone Apple Guava 500ml', 6000, 8500, 'botol'], ['Mizone Orange Lime 500ml', 6000, 8500, 'botol'],
            ['Fanta Strawberry 390ml', 5500, 7500, 'botol'], ['Fanta Orange 390ml', 5500, 7500, 'botol'],
            ['Sprite 390ml', 5500, 7500, 'botol'], ['Coca-Cola 390ml', 5500, 7500, 'botol'],
            ['Teh Pucuk Harum 350ml', 3500, 5000, 'botol'], ['Teh Pucuk Harum 600ml', 5500, 7500, 'botol'],
            ['Nu Green Tea 330ml', 4500, 6500, 'botol'], ['Nu Aqua Galon 19L', 15000, 19000, 'galon'],
            ['Le Minerale 600ml', 3000, 4500, 'botol'], ['Club Soda 330ml', 4000, 5500, 'botol'],
            ['Kopi Good Day 250ml', 5000, 7000, 'botol'], ['Kopi Luwak 250ml', 5000, 7000, 'botol'],
            ['Susu Ultra Milk 250ml', 5000, 7000, 'kotak'], ['Susu Indomilk 250ml', 4500, 6500, 'kotak'],
            ['Yakult Original 5pc', 15000, 20000, 'pack'], ['Cimory Yogurt Drink 250ml', 8000, 11000, 'botol'],
            ['Es Teh 500ml', 4000, 6000, 'botol'], ['Marimas Jeruk Sachet', 800, 1500, 'sachet'],
            ['Hilo Teen Coklat 200ml', 5500, 8000, 'kotak'], ['Ale-Ale Mangga 250ml', 3000, 5000, 'botol'],
            ['Big Cola 1500ml', 9000, 12000, 'botol'], ['Mountea Lemon 330ml', 3500, 5000, 'botol'],
            ['Vitacimin Jeruk Sachet', 1500, 2500, 'sachet'], ['Hydro Coco Asli 500ml', 8000, 11000, 'botol'],
            ['Chatime Milk Tea 350ml', 12000, 17000, 'botol'], ['OKF Aloe Vera 500ml', 9000, 13000, 'botol'],
            ['Kopiko 78°C Coffee 240ml', 7000, 10000, 'botol'], ['Kopi ABC Susu 200ml', 5500, 8000, 'botol'],
            ['Pepsi 390ml', 5000, 7500, 'botol'], ['7Up Lime 390ml', 5000, 7000, 'botol'],
            ['Nutrisari Jeruk Sachet', 1000, 2000, 'sachet'], ['You C1000 Vitamin 140ml', 7000, 10000, 'botol'],
            ['Buavita Mangga 250ml', 7000, 10000, 'kotak'], ['Buavita Jambu 250ml', 7000, 10000, 'kotak'],
            ['Hotto Minuman Sarang Walet 240ml', 12000, 17000, 'botol'], ['Susu Frisian Flag 200ml', 5000, 7500, 'kotak'],
            ['Es Jeruk Kemasan 200ml', 2500, 4000, 'kotak'], ['Jahe Wangi Sachet', 3000, 5000, 'sachet'],
            ['Kuku Bima Ener-G Sachet', 1500, 2500, 'sachet'], ['Extra Joss Sachet', 1500, 2500, 'sachet'],
            ['Hemaviton Jreng Sachet', 2000, 3500, 'sachet'], ['M-150 Energy Drink 150ml', 5000, 7500, 'botol'],
        ];
        return $this->formatProduk($produk, [50, 150], 'botol');
    }

    private function getMakananRingan(): array
    {
        $produk = [
            ['Chitato Sapi Panggang 68g', 9000, 12500, 'pcs'], ['Chitato Ayam 68g', 9000, 12500, 'pcs'],
            ['Pringles Original 107g', 30000, 40000, 'kaleng'], ['Lays Classic 75g', 12000, 16000, 'pcs'],
            ['Oreo Original 119g', 10000, 14000, 'pcs'], ['Oreo Coklat 119g', 10000, 14000, 'pcs'],
            ['Monde Butter Cookies 200g', 18000, 25000, 'pcs'], ['Roma Kelapa 135g', 5500, 8000, 'pcs'],
            ['Roma Malkist 135g', 5500, 8000, 'pcs'], ['Roti Sari Roti Tawar', 12000, 16000, 'pcs'],
            ['Ritz Crackers Original 97g', 12000, 16500, 'pcs'], ['Happydent White Permen', 500, 1000, 'pcs'],
            ['Mentos Strawberry', 2000, 3500, 'pcs'], ['Wafer Tango Coklat 150g', 10000, 14000, 'pcs'],
            ['Wafer Tango Vanilla 150g', 10000, 14000, 'pcs'], ['Beng-Beng Share It 40g', 5000, 7500, 'pcs'],
            ['Silver Queen Almonds 58g', 12000, 17000, 'pcs'], ['Silver Queen Cashew 58g', 12000, 17000, 'pcs'],
            ['Choki-Choki 5pc', 4000, 6000, 'pcs'], ['Gery Chocolatos 24g', 2500, 4000, 'pcs'],
            ['Richeese Nabati 145g', 9000, 12500, 'pcs'], ['Macaroni Schotel 90g', 7000, 10000, 'pcs'],
            ['Keripik Singkong 100g', 7000, 10500, 'pcs'], ['Kacang Garuda 120g', 9000, 13000, 'pcs'],
            ['Kacang Dua Kelinci 140g', 10000, 14000, 'pcs'], ['Cornetto Disc Original', 7000, 10000, 'pcs'],
            ['Momogi Stick Coklat 5g', 500, 1000, 'pcs'], ['Choco Pia 180g', 12000, 17000, 'pcs'],
            ['Biskuit Marie Regal 225g', 9000, 12500, 'pcs'], ['Hello Panda Coklat 43g', 8000, 11500, 'pcs'],
            ['Stick Wafer Coklat 45g', 3000, 5000, 'pcs'], ['Biscuits Monde Marie 350g', 18000, 25000, 'pcs'],
            ['Pocky Coklat 47g', 11000, 15500, 'pcs'], ['Taro Net Jagung 65g', 5500, 8000, 'pcs'],
            ['Qtela Tempe 160g', 12000, 17000, 'pcs'], ['Cheetos Crunchy 50g', 5500, 8000, 'pcs'],
            ['Combos Pretzel Pizza 48g', 12000, 17000, 'pcs'], ['Nutella Go 52g', 18000, 25000, 'pcs'],
            ['Jelly Yupi Gummy 100g', 10000, 14000, 'pcs'], ['Permen Kopiko 175g', 18000, 25000, 'pcs'],
            ['Kripik Pisang 200g', 10000, 15000, 'pcs'], ['Emping Melinjo 200g', 15000, 22000, 'pcs'],
            ['Kerupuk Udang 200g', 12000, 18000, 'pcs'], ['Rempeyek Kacang 150g', 12000, 18000, 'pcs'],
            ['Onde-Onde 5pcs', 7000, 12000, 'pcs'], ['Donat Mini 6pcs', 8000, 14000, 'pcs'],
            ['Sosis So Nice 500g', 22000, 32000, 'pcs'], ['Bakso Ikan 500g', 20000, 30000, 'pcs'],
            ['Abon Sapi 100g', 20000, 30000, 'pcs'], ['Keripik Tempe 100g', 10000, 16000, 'pcs'],
        ];
        return $this->formatProduk($produk, [100, 200], 'pcs');
    }

    private function getMieBubur(): array
    {
        $produk = [
            ['Indomie Goreng 85g', 2800, 4500, 'pcs'], ['Indomie Soto 75g', 2800, 4500, 'pcs'],
            ['Indomie Ayam Bawang 75g', 2800, 4500, 'pcs'], ['Indomie Rendang 85g', 3000, 5000, 'pcs'],
            ['Mie Sedaap Goreng 87g', 2800, 4500, 'pcs'], ['Mie Sedaap Soto 77g', 2800, 4500, 'pcs'],
            ['Supermie Goreng 82g', 2500, 4000, 'pcs'], ['Sarimi Soto Koya 75g', 2500, 4000, 'pcs'],
            ['Pop Mie Goreng 75g', 4000, 6500, 'pcs'], ['Pop Mie Ayam 75g', 4000, 6500, 'pcs'],
            ['Gaga 100 Goreng 84g', 3000, 5000, 'pcs'], ['Mie ABC Goreng 75g', 2800, 4500, 'pcs'],
            ['Nissin Ramen Sapi 75g', 3000, 5000, 'pcs'], ['Mamasuka Mie Goreng 90g', 3000, 5000, 'pcs'],
            ['Bubur Ayam Instan 45g', 5000, 7500, 'pcs'], ['Bubur Kacang Hijau Instant 35g', 5500, 8000, 'pcs'],
            ['Oatmeal Quaker 40g sachet', 6000, 9000, 'sachet'], ['Promina Bubur 120g', 8000, 12000, 'pcs'],
            ['Milna Biscuit Bayi 130g', 15000, 22000, 'pcs'], ['Cerelac Beras 200g', 25000, 36000, 'pcs'],
        ];
        return $this->formatProduk($produk, [200, 500], 'pcs');
    }

    private function getBumbuRempah(): array
    {
        $produk = [
            ['Royco Bumbu Serbaguna Ayam 250g', 12000, 17000, 'pcs'], ['Royco Sapi 250g', 12000, 17000, 'pcs'],
            ['Masako Ayam 100g', 5000, 7500, 'pcs'], ['Masako Sapi 100g', 5000, 7500, 'pcs'],
            ['Magi Kaldu Ayam 100g', 6000, 9000, 'pcs'], ['Magi Kaldu Sapi 100g', 6000, 9000, 'pcs'],
            ['Kecap Manis ABC 135ml', 5000, 7500, 'botol'], ['Kecap Bango 140ml', 6000, 9000, 'botol'],
            ['Saus Sambal ABC 135ml', 5000, 7500, 'botol'], ['Saus Tomat ABC 135ml', 5000, 7500, 'botol'],
            ['Gula Pasir 1kg', 14000, 18000, 'kg'], ['Gula Merah 1kg', 18000, 25000, 'kg'],
            ['Garam Refina 500g', 3000, 5000, 'pcs'], ['Minyak Goreng Bimoli 1L', 15000, 21000, 'botol'],
            ['Minyak Goreng Sania 1L', 14000, 20000, 'botol'], ['Tepung Terigu Bogasari 1kg', 11000, 16000, 'pcs'],
            ['Bumbu Nasi Goreng Bamboe', 4000, 6500, 'sachet'], ['Bumbu Rendang Bamboe', 5000, 8000, 'sachet'],
            ['Bumbu Opor Bamboe', 5000, 8000, 'sachet'], ['Cabe Bubuk 50g', 5000, 8000, 'pcs'],
        ];
        return $this->formatProduk($produk, [50, 100], 'pcs');
    }

    private function getSusuDairy(): array
    {
        $produk = [
            ['Susu Beruang 189ml', 8000, 12000, 'kaleng'], ['Susu Kental Manis Cap Nona 375g', 10000, 15000, 'kaleng'],
            ['Susu Kental Manis Frisian Flag 370g', 10000, 15000, 'kaleng'], ['Indomilk Susu Full Cream 1L', 15000, 22000, 'kotak'],
            ['Ultra Milk Full Cream 1L', 16000, 23000, 'kotak'], ['Dancow Full Cream 400g', 35000, 50000, 'pcs'],
            ['Milo Activ-Go 200g', 18000, 26000, 'pcs'], ['Ovomaltine Coklat 400g', 45000, 65000, 'pcs'],
            ['Nesquik Coklat 200g', 18000, 26000, 'pcs'], ['SGM Eksplor 1+ 400g', 55000, 80000, 'pcs'],
        ];
        return $this->formatProduk($produk, [50, 100], 'kaleng');
    }

    private function getRokok(): array
    {
        $produk = [
            ['Gudang Garam Merah 12s', 20000, 25000, 'bungkus'], ['Gudang Garam Filter 12s', 22000, 27000, 'bungkus'],
            ['Sampoerna Mild 16s', 29000, 35000, 'bungkus'], ['Sampoerna Kretek 12s', 20000, 25000, 'bungkus'],
            ['Djarum Super 12s', 22000, 27000, 'bungkus'], ['Djarum Black 16s', 28000, 35000, 'bungkus'],
            ['LA Bold 16s', 27000, 33000, 'bungkus'], ['Marlboro Red 20s', 35000, 43000, 'bungkus'],
            ['Camel Filter 20s', 30000, 38000, 'bungkus'], ['Class Mild 16s', 22000, 28000, 'bungkus'],
        ];
        return $this->formatProduk($produk, [100, 300], 'bungkus');
    }

    private function getAlatTulis(): array
    {
        $produk = [
            ['Pulpen Pilot G2 Hitam', 6000, 9500, 'pcs'], ['Pulpen Snowman BX-7 Hitam', 3000, 5000, 'pcs'],
            ['Buku Tulis Sidu 38 Lembar', 3500, 6000, 'pcs'], ['Buku Tulis Kiky 58 Lembar', 5000, 8000, 'pcs'],
            ['Pensil 2B Faber-Castell', 4000, 6500, 'pcs'], ['Penghapus Steadler', 3000, 5000, 'pcs'],
            ['Rautan Penghapus 2in1', 3500, 6000, 'pcs'], ['Stabilo Warna Boss', 5000, 8000, 'pcs'],
            ['Lem UHU 21g', 6000, 9500, 'pcs'], ['Lem Alteco Serbaguna', 5000, 8000, 'pcs'],
            ['Lakban Bening 48mm', 7000, 11000, 'pcs'], ['Kertas HVS A4 80gsm 100lbr', 8000, 13000, 'pcs'],
            ['Amplop Polos C4 10pc', 4000, 6500, 'pcs'], ['Stapler Rapid 24/6', 18000, 27000, 'pcs'],
            ['Isi Staples 24/6 Kangaro', 3000, 5000, 'kotak'], ['Klip Kertas Besar 33mm 10pc', 3000, 5000, 'kotak'],
            ['Spidol Whiteboard Snowman', 5000, 8000, 'pcs'], ['Cutter Besar Kenko', 7000, 11000, 'pcs'],
            ['Tipe-X Koreksi Pilot', 4000, 6500, 'pcs'], ['Penggaris 30cm Joyko', 3000, 5000, 'pcs'],
        ];
        return $this->formatProduk($produk, [100, 300], 'pcs');
    }

    private function getSabunKebersihan(): array
    {
        $produk = [
            ['Sabun Mandi Lifebuoy 85g', 3500, 6000, 'pcs'], ['Sabun Mandi Dettol 85g', 5000, 8000, 'pcs'],
            ['Shampo Clear Sachet 10ml', 1000, 2000, 'sachet'], ['Shampo Pantene Sachet 10ml', 1000, 2000, 'sachet'],
            ['Rinso Deterjen 60g', 1500, 2500, 'sachet'], ['Attack Deterjen 65g', 1500, 2500, 'sachet'],
            ['Sunlight Pencuci Piring 200ml', 4000, 6500, 'botol'], ['Mama Lemon 200ml', 4000, 6500, 'botol'],
            ['Wipol Pembersih Lantai 450ml', 10000, 15000, 'botol'], ['Bayclin Pemutih 500ml', 9000, 14000, 'botol'],
            ['Pasta Gigi Pepsodent 75g', 7000, 11000, 'pcs'], ['Pasta Gigi Close Up 80g', 7000, 11000, 'pcs'],
            ['Sikat Gigi Formula', 5000, 8000, 'pcs'], ['Kapas Kecantikan 50g', 6000, 9500, 'pcs'],
            ['Pembalut Wanita Charm 8pc', 9000, 14000, 'pcs'], ['Popok Bayi Merries S 6pc', 22000, 33000, 'pcs'],
            ['Tisu Paseo Pocket 4 Ply', 3000, 5000, 'pcs'], ['Tisu Basah Mitu 10 lembar', 3500, 6000, 'pcs'],
            ['Deodoran Rexona Roll-On 25ml', 12000, 18000, 'pcs'], ['Cologne Axe 50ml', 20000, 30000, 'botol'],
        ];
        return $this->formatProduk($produk, [100, 200], 'pcs');
    }

    private function getKecantikan(): array
    {
        $produk = [
            ['Pelembab Pond\'s White Beauty 50g', 20000, 30000, 'pcs'], ['Pond\'s Age Miracle 50g', 30000, 45000, 'pcs'],
            ['Wardah Sunscreen SPF 30 15ml', 15000, 22000, 'pcs'], ['Emina CC Cream 20ml', 18000, 27000, 'pcs'],
            ['Lipstik Implora 03', 18000, 27000, 'pcs'], ['Bedak Pixy UV Whitening', 25000, 37000, 'pcs'],
            ['Micellar Water Garnier 125ml', 25000, 37000, 'botol'], ['Pembersih Wajah Facial Foam Ovale', 18000, 28000, 'pcs'],
            ['Minyak Telon Lang 60ml', 15000, 23000, 'botol'], ['Revlon Lip Butter 3.7g', 35000, 53000, 'pcs'],
        ];
        return $this->formatProduk($produk, [50, 150], 'pcs');
    }

    private function getFrozenFood(): array
    {
        $produk = [
            ['Nugget So Good 500g', 30000, 43000, 'pcs'], ['Nugget Fiesta 500g', 28000, 40000, 'pcs'],
            ['Sosis Kimbo 500g', 25000, 36000, 'pcs'], ['Sosis Bernardi 500g', 28000, 40000, 'pcs'],
            ['Bakso Sapi Cedea 500g', 30000, 43000, 'pcs'], ['Udang Rebon Beku 250g', 25000, 37000, 'pcs'],
            ['Calamari Ring Finna 500g', 32000, 46000, 'pcs'], ['Kwetiau Instan 250g', 12000, 18000, 'pcs'],
            ['Dimsum Udang HAP 300g', 25000, 37000, 'pcs'], ['Spring Roll Isian Sayur 500g', 25000, 37000, 'pcs'],
        ];
        // Frozen food: masa expired lebih pendek
        return $this->formatProduk($produk, [100, 300], 'pcs');
    }

    /**
     * Helper: format array produk ke struktur standar.
     */
    private function formatProduk(array $produk, array $minStockRange, string $defaultUnit): array
    {
        return array_map(function ($item) use ($minStockRange, $defaultUnit) {
            return [
                'name'      => $item[0],
                'buy_price' => $item[1],
                'sell_price'=> $item[2],
                'unit'      => $item[3] ?? $defaultUnit,
                'min_stock' => rand($minStockRange[0], $minStockRange[1]),
            ];
        }, $produk);
    }
}