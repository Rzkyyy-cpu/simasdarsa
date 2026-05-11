<x-mail::message>
# Peringatan Stok Kedaluwarsa

Halo, berikut adalah daftar batch stok yang akan segera kedaluwarsa dalam 30 hari ke depan atau sudah kedaluwarsa.

<x-mail::table>
| Produk | Batch | Tgl Expired | Sisa Hari | Stok |
| :--- | :--- | :--- | :--- | :--- |
@foreach($batches as $batch)
| {{ $batch->product->name }} | {{ $batch->batch_code }} | {{ $batch->expired_date->format('d/m/Y') }} | {{ $batch->days_until_expired }} | {{ $batch->current_quantity }} |
@endforeach
</x-mail::table>

Mohon segera lakukan tindakan verifikasi atau pembersihan stok di rak.

<x-mail::button :url="route('stok.expiry-monitor')">
Lihat Detail di Dashboard
</x-mail::button>

Terima kasih,<br>
Sistem {{ config('app.name') }}
</x-mail::message>
