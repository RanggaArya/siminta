<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resume Peminjaman – {{ $periodeLabel }}</title>
    <style>
        body{ font-family: DejaVu Sans, sans-serif; font-size:11.5px; color:#111; }
        table{ width:100%; border-collapse:collapse; }
        th,td{ border:1px solid #333; padding:6px 8px; vertical-align:top; }
        th{ background:#efefef; }
        .nowrap{ white-space:nowrap; }
    </style>
</head>
<body>
    <table style="width: 100%; border-bottom: 3px double #000000; padding-bottom: 10px;">
        <tr>
            <td style="width: 20%; text-align: right; border: 0; vertical-align: top;">
                <img src="{{ public_path('images/RSU.png') }}" alt="Logo RS" style="width: 80px; height: auto;">
            </td>

            <td style="width: 60%; text-align: center; border: 0; vertical-align: top;">
                <h3 style="margin: 0; font-size: 16px; font-weight: normal;">YAYASAN RSU MITRA PARAMEDIKA</h3>
                <h2 style="margin: 0; font-size: 28px; font-weight: bold;"> RSU MITRA PARAMEDIKA</h2>
                <p style="margin: 0; font-size: 14px;">
                    Jl. Raya ngemplak, Kemasan, Widodomartani, Ngemplak
                </p>
                <p style="margin: 0; font-size: 14px;">
                    Sleman, Yogyakarta Telp. (0274) 4461098
                </p>
                <p style="margin: 0; font-size: 14px;">
                    Web: rsumipayk.co.id Email: rsumitraparamedika@yahoo.com
                </p>
            </td>
            <td style="width: 20%; text-align: left; border: 0; vertical-align: top;">
                <img src="{{ public_path('images/KARS.png') }}" alt="Logo RS" style="width: 110px; height: auto;">
            </td>
        </tr>
    </table>
    <br><br><br>

    <table style="width: 100%; padding-bottom: 10px;">
        <tr>
            <td style="width: 60%; text-align: center; border: 0; vertical-align: top;">
                <h3 style="margin: 0; font-size: 16px; font-weight: normal;">RESUME PEMINJAMAN BARANG IT RSU MITRA
                    PARAMEDIKA {{ $periodeLabel }}
                </h3>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No. Inventaris</th>
                <th>Nama Barang</th>
                <th>Merk/Tipe</th>
                <th>Kondisi</th>
                <th>Peminjam</th>
                <th>Email</th>
                <th class="nowrap">Tgl. Mulai</th>
                <th class="nowrap">Tgl. Selesai</th>
                <th>Status</th>
                <th>Alasan</th>
                <th>Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td class="nowrap">{{ $row->nomor_inventaris ?? '-' }}</td>
                <td>{{ $row->nama_barang ?? '-' }}</td>
                <td>{{ $row->merk ?? '-' }}</td>
                <td>{{ $row->kondisi_terakhir ?? '-' }}</td>
                <td>{{ $row->pihak_kedua_nama ?? '-' }}</td>
                <td>{{ $row->peminjam_email ?? '-' }}</td>
                <td class="nowrap">
                    {{ $row->tanggal_mulai ? \Illuminate\Support\Carbon::parse($row->tanggal_mulai)->locale('id')->translatedFormat('d M Y') : '-' }}
                </td>
                <td class="nowrap">
                    {{ $row->tanggal_selesai ? \Illuminate\Support\Carbon::parse($row->tanggal_selesai)->locale('id')->translatedFormat('d M Y') : '-' }}
                </td>
                <td>{{ $row->status ?? '-' }}</td>
                <td>{{ $row->alasan_pinjam ?? '-' }}</td>
                <td>{{ $row->dicatat_oleh ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="11">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
