<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resume Penarikan – {{ $periodeLabel }}</title>
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

        .nowrap {
            white-space: nowrap;
        }

        .right {
            text-align: right;
        }

        .small {
            font-size: 10px;
            color: #555;
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
    <br><br>

    <table style="width: 100%; padding-bottom: 10px;">
        <tr>
            <td style="width: 60%; text-align: center; border: 0; vertical-align: top;">
                <h3 style="margin: 0; font-size: 16px; font-weight: normal;">RESUME PENARIKAN BARANG IT RSU MITRA
                    PARAMEDIKA {{ $periodeLabel }}
                </h3>
            </td>
        </tr>
    </table>
    <br><br>

    @isset($summary)
    <table style="margin-bottom:10px;">
        <tr>
            <th class="right">Total</th>
            <th class="right">Rusak</th>
            <th class="right">TL Perbaikan</th>
            <th class="right">TL Ganti Baru</th>
            <th class="right">TL Pindahan</th>
        </tr>
        <tr>
            <td class="right">{{ $summary['total'] ?? 0 }}</td>
            <td class="right">{{ $summary['rusak'] ?? 0 }}</td>
            <td class="right">{{ $summary['tl_perb'] ?? 0 }}</td>
            <td class="right">{{ $summary['tl_baru'] ?? 0 }}</td>
            <td class="right">{{ $summary['tl_pind'] ?? 0 }}</td>
        </tr>
    </table>
    @endisset

    <table>
        <thead>
            <tr>
                <th>No. Inventaris</th>
                <th>Nama Perangkat</th>
                <th>Tipe</th>
                <th>Lokasi Snapshot</th>
                <th class="nowrap">Tgl. Penarikan</th>
                <th>Alasan</th>
                <th>Tindak Lanjut</th>
                <th>Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            @php
            $alasan = $row->alasan_penarikan;
            if (is_string($alasan)) {
            $dec = json_decode($alasan, true);
            $alasan = is_array($dec) ? $dec : [$alasan];
            }
            $alasanTxt = collect((array)$alasan)->filter()->implode(', ');
            if (!empty($row->alasan_lainnya)) {
            $alasanTxt = trim($alasanTxt ? "$alasanTxt; $row->alasan_lainnya" : $row->alasan_lainnya);
            }
            $tl = $row->tindak_lanjut_tipe . (filled($row->tindak_lanjut_detail) ? " ({$row->tindak_lanjut_detail})" : '');
            @endphp
            <tr>
                <td class="nowrap">{{ $row->nomor_inventaris ?? '-' }}</td>
                <td>{{ $row->nama_perangkat ?? '-' }}</td>
                <td>{{ $row->tipe ?? '-' }}</td>
                <td>{{ $row->lokasi_snapshot ?? '-' }}</td>
                <td class="nowrap">
                    {{ $row->tanggal_penarikan
                        ? \Illuminate\Support\Carbon::parse($row->tanggal_penarikan)->locale('id')->translatedFormat('d M Y')
                        : '-' }}
                </td>
                <td>{{ $alasanTxt ?: '-' }}</td>
                <td>{{ $tl ?: '-' }}</td>
                <td>{{ $row->dicatat_oleh ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>