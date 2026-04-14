<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resume Mutasi – {{ $periodeLabel }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11.5px;
            color: #111;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #efefef;
        }

        h2 {
            margin: 0 0 10px;
        }

        .small {
            font-size: 10px;
            color: #555;
        }

        .right {
            text-align: right;
        }

        .mt {
            margin-top: 10px;
        }

        .nowrap {
            white-space: nowrap;
        }
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
                <h3 style="margin: 0; font-size: 16px; font-weight: normal;">RESUME MUTASI BARANG IT RSU MITRA
                    PARAMEDIKA {{ $periodeLabel }}
                </h3>
            </td>
        </tr>
    </table>
    <br><br>

    <!-- <table class="mt" style="margin-bottom:10px;">
        <tr>
            <th class="right">Total</th>
            <th class="right">Diterima</th>
            <th class="right">Pending</th>
            <th class="right">Kondisi Baik</th>
            <th class="right">Kondisi Rusak</th>
        </tr>
        <tr>
            <td class="right">{{ $summary['total'] }}</td>
            <td class="right">{{ $summary['diterima'] }}</td>
            <td class="right">{{ $summary['pending'] }}</td>
            <td class="right">{{ $summary['baik'] }}</td>
            <td class="right">{{ $summary['rusak'] }}</td>
        </tr>
    </table> -->

    <table>
        <thead>
            <tr>
                <th>No. Inventaris</th>
                <th>Nama Perangkat</th>
                <th>Tipe</th>
                <th>Kondisi</th>
                <th>Lokasi Asal</th>
                <th>Lokasi Tujuan</th>
                <th class="nowrap">Tgl. Mutasi</th>
                <th class="nowrap">Tgl. Diterima</th>
                <th>Alasan</th>
                <th>Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td class="nowrap">{{ $row->nomor_inventaris ?? '-' }}</td>
                <td>{{ $row->nama_perangkat ?? '-' }}</td>
                <td>{{ $row->tipe ?? '-' }}</td>
                <td>{{ $row->kondisi ?? '-' }}</td>
                <td>{{ $row->lokasi_asal ?? '-' }}</td>
                <td>{{ $row->lokasi_tujuan ?? '-' }}</td>
                <td class="nowrap">
                    {{ $row->tanggal_mutasi
                ? \Illuminate\Support\Carbon::parse($row->tanggal_mutasi)->locale('id')->translatedFormat('d M Y')
                : '-' }}
                </td>
                <td class="nowrap">
                    {{ $row->tanggal_diterima
                ? \Illuminate\Support\Carbon::parse($row->tanggal_diterima)->locale('id')->translatedFormat('d M Y')
                : '-' }}
                </td>
                <td>{{ $row->alasan_mutasi ?? '-' }}</td>
                <td>{{ $row->dicatat_oleh ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>

    </table>

</body>

</html>