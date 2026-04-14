@component('mail::message')
# 📢 Pengingat Pengembalian Barang

Halo **{{ $peminjaman->pihak_kedua_nama ?? '-' }}**,  
Batas waktu pengembalian perangkat tinggal **3 hari lagi**.

### 🔧 Detail Peminjaman
- **Barang:** {{ $peminjaman->nama_barang ?? '-' }}
- **Nomor Inventaris:** {{ $peminjaman->nomor_inventaris ?? '-' }}
- **Batas Pengembalian:** {{ optional($peminjaman->tanggal_selesai)->format('d F Y') }} (**3 Hari Lagi**)

@component('mail::panel')
Pastikan barang dikembalikan tepat waktu. Terima kasih!
@endcomponent

Salam,  
**Tim SIRS**
@endcomponent
