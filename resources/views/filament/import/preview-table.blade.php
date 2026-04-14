<div>
  @php
  /** @var \App\Filament\Pages\ImportPerangkat $this */
  $headers = $this->headers ?? [];
  $rows = $this->previewRows ?? [];
  $limit = $this->previewLimit ?? 50;
  $total = $this->totalRows ?? 0;
  @endphp

  <div class="filament-tables-container overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr>
          <th class="px-3 py-2 text-left text-gray-600">#</th>
          @foreach($headers as $h)
          <th class="px-3 py-2 text-left text-gray-600">{{ \Illuminate\Support\Str::headline($h) }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $i => $r)
        <tr class="border-t">
          <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
          @foreach ($headers as $h)
          @php
          $v = $r[$h] ?? '';
          if (!is_scalar($v)) {
          $v = json_encode($v);
          }
          $s = trim((string) $v);
          @endphp
          <td class="px-3 py-2 whitespace-nowrap">
            {{ \Illuminate\Support\Str::limit($s, 120) }}
          </td>
          @endforeach
        </tr>
        @empty
        <tr>
          <td colspan="{{ count($headers) + 1 }}" class="px-3 py-4 text-center text-gray-500">
            Tidak ada data terbaca dari file.
          </td>
        </tr>
        @endforelse
      </tbody>

    </table>
  </div>

  <div class="mt-2 text-xs text-gray-500">
    Menampilkan maksimal {{ $limit }} baris dari total {{ $total }} baris.
    Kolom sudah dinormalisasi (mis. <code>no inventaris</code> → <code>nomor_inventaris</code>, <code>tahun</code> → <code>tahun_pengadaan</code>, dst).
  </div>
</div>