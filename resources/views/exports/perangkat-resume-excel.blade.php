<table>
    <thead>
        <tr>
            <th rowspan="3" style="font-weight: bold; vertical-align: middle; text-align: center;">Nama Perangkat</th>
            <th colspan="6" style="font-weight: bold; text-align: center;">Status</th>
            <th rowspan="2" colspan="2" style="font-weight: bold; vertical-align: middle; text-align: center;">Total
            </th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; text-align: center;">Digunakan</th>
            <th colspan="2" style="font-weight: bold; text-align: center;">Dalam Perbaikan</th>
            <th colspan="2" style="font-weight: bold; text-align: center;">Tidak Digunakan</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center;">Jumlah</th>
            <th style="font-weight: bold; text-align: center;">Harga</th>

            <th style="font-weight: bold; text-align: center;">Jumlah</th>
            <th style="font-weight: bold; text-align: center;">Harga</th>

            <th style="font-weight: bold; text-align: center;">Jumlah</th>
            <th style="font-weight: bold; text-align: center;">Harga</th>

            <th style="font-weight: bold; text-align: center;">Jumlah</th>
            <th style="font-weight: bold; text-align: center;">Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
        <tr>
            <td>{{ $row->nama_perangkat }}</td>

            <td>{{ $row->aktif_count }}</td>
            <td>Rp. {{ number_format($row->aktif_sum, 0, ',', '.') }}</td>

            <td>{{ $row->rusak_count }}</td>
            <td>Rp. {{ number_format($row->rusak_sum, 0, ',', '.') }}</td>

            <td>{{ $row->tidak_digunakan_count }}</td>
            <td>Rp. {{ number_format($row->tidak_digunakan_sum, 0, ',', '.') }}</td>

            <td>{{ $row->total_count }}</td>
            <td>Rp. {{ number_format($row->total_sum, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="font-weight: bold;">Grand Total</td>

            <td style="font-weight: bold;">{{ $grandTotal['aktif_count'] }}</td>
            <td style="font-weight: bold;">Rp. {{ number_format($grandTotal['aktif_sum'], 0, ',', '.') }}</td>

            <td style="font-weight: bold;">{{ $grandTotal['rusak_count'] }}</td>
            <td style="font-weight: bold;">Rp. {{ number_format($grandTotal['rusak_sum'], 0, ',', '.') }}</td>

            <td style="font-weight: bold;">{{ $grandTotal['tidak_digunakan_count'] }}</td>
            <td style="font-weight: bold;">Rp. {{ number_format($grandTotal['tidak_digunakan_sum'], 0, ',', '.') }}</td>

            <td style="font-weight: bold;">{{ $grandTotal['total_count'] }}</td>
            <td style="font-weight: bold;">Rp. {{ number_format($grandTotal['total_sum'], 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>