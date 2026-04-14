<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Resume Inventaris Perangkat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background-color: #f2f2f2;
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
                    <h3 style="margin: 0; font-size: 16px; font-weight: normal;">RESUME INVENTARIS BARANG RSU MITRA
                        PARAMEDIKA {{ $bulanTahun }}
                    </h3>
                </td>
            </tr>
        </table>
        <br><br>

        <table>
            <thead>
                <tr>
                    <th rowspan="3"
                        style="font-weight: bold; font-size: 14px; vertical-align: middle; text-align: center;">Nama
                        Perangkat</th>
                    <th colspan="6" style="font-weight: bold; font-size: 14px; text-align: center;">Status</th>
                    <th rowspan="2" colspan="2"
                        style="font-weight: bold; font-size: 14px; vertical-align: middle; text-align: center;">
                        Total
                    </th>
                </tr>
                <tr>
                    <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center;">Digunakan</th>
                    <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center;">Dalam Perbaikan</th>
                    <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center;">Tidak
                        Digunakan</th>
                </tr>
                <tr>
                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Jumlah</th>
                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Harga</th>

                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Jumlah</th>
                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Harga</th>

                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Jumlah</th>
                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Harga</th>

                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Jumlah</th>
                    <th style="font-weight: bold; font-size: 14px; text-align: center;">Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row->nama_perangkat }}</td>

                        <td style="text-align: right;">{{ $row->aktif_count }}</td>
                        <td style="text-align: right;">Rp. {{ number_format($row->aktif_sum, 0, ',', '.') }}</td>

                        <td style="text-align: right;">{{ $row->rusak_count }}</td>
                        <td style="text-align: right;">Rp. {{ number_format($row->rusak_sum, 0, ',', '.') }}</td>

                        <td style="text-align: right;">{{ $row->tidak_digunakan_count }}</td>
                        <td style="text-align: right;">Rp. {{ number_format($row->tidak_digunakan_sum, 0, ',', '.') }}</td>

                        <td style="text-align: right;">{{ $row->total_count }}</td>
                        <td style="text-align: right;">Rp. {{ number_format($row->total_sum, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td style="font-weight: bold;  font-size: 14px;">Grand Total</td>

                    <td style="font-weight: bold; text-align: right;">{{ $grandTotal['aktif_count'] }}</td>
                    <td style="font-weight: bold; text-align: right;">Rp. {{ number_format($grandTotal['aktif_sum'], 0, ',', '.') }}</td>

                    <td style="font-weight: bold; text-align: right;">{{ $grandTotal['rusak_count'] }}</td>
                    <td style="font-weight: bold; text-align: right;">Rp. {{ number_format($grandTotal['rusak_sum'], 0, ',', '.') }}</td>

                    <td style="font-weight: bold; text-align: right;">{{ $grandTotal['tidak_digunakan_count'] }}</td>
                    <td style="font-weight: bold; text-align: right;">Rp. {{ number_format($grandTotal['tidak_digunakan_sum'], 0, ',', '.') }}</td>

                    <td style="font-weight: bold; text-align: right;">{{ $grandTotal['total_count'] }}</td>
                    <td style="font-weight: bold; text-align: right;">Rp. {{ number_format($grandTotal['total_sum'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

</body>

</html>