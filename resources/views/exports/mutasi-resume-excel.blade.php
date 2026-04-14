<table>
    <thead>
        <tr>
            <th colspan="7" align="left">RESUME MUTASI PERANGKAT – {{ $periodeLabel }}</th>
        </tr>
        <tr>
            <th>Lokasi Tujuan</th>
            <th>Total Mutasi</th>
            <th>Diterima</th>
            <th>Pending</th>
            <th>Kondisi Baik</th>
            <th>Kondisi Rusak</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                <td>{{ $row->lokasi_tujuan ?? '-' }}</td>
                <td>{{ (int)$row->total_mutasi }}</td>
                <td>{{ (int)$row->diterima_count }}</td>
                <td>{{ (int)$row->pending_count }}</td>
                <td>{{ (int)$row->kondisi_baik }}</td>
                <td>{{ (int)$row->kondisi_rusak }}</td>
            </tr>
        @endforeach
        <tr>
            <td><strong>GRAND TOTAL</strong></td>
            <td><strong>{{ $grandTotal['total_mutasi'] }}</strong></td>
            <td><strong>{{ $grandTotal['diterima_count'] }}</strong></td>
            <td><strong>{{ $grandTotal['pending_count'] }}</strong></td>
            <td><strong>{{ $grandTotal['kondisi_baik'] }}</strong></td>
            <td><strong>{{ $grandTotal['kondisi_rusak'] }}</strong></td>
        </tr>
    </tbody>
</table>
