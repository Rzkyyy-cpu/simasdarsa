<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockBatch;
use App\Models\User;
use App\Mail\ExpiryNotification;
use Illuminate\Support\Facades\Mail;

class CheckExpiredStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek stok yang akan kedaluwarsa dan kirim notifikasi email ke Admin/Manajer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiringBatches = StockBatch::with('product')
            ->where('current_quantity', '>', 0)
            ->where('expired_date', '<=', now()->addDays(30)->toDateString())
            ->get();

        if ($expiringBatches->isEmpty()) {
            $this->info('Tidak ada stok yang mendekati kedaluwarsa.');
            return;
        }

        $recipients = User::all()->filter(function($user) {
            return $user->hasRole('pimpinan') || $user->hasRole('manager');
        });

        if ($recipients->isEmpty()) {
            $this->warn('Tidak ada penerima (Admin/Manajer) yang ditemukan.');
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new ExpiryNotification($expiringBatches));
                $this->info("Email dikirim ke: {$recipient->email}");
            } catch (\Exception $e) {
                $this->error("Gagal kirim email ke {$recipient->email}: " . $e->getMessage());
            }
        }

        $this->info('Proses pengiriman notifikasi selesai.');
    }
}
