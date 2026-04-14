<div class="space-y-2">
    @php
        $dupeCount = count($this->dupes ?? []);
    @endphp
    <div class="text-sm">
        <div><strong>Total baris di file:</strong> {{ $this->totalRows }}</div>
        <div><strong>Jumlah duplikat ditemukan:</strong> {{ $dupeCount }}</div>
    </div>

    @if($dupeCount > 0)
        <div class="text-sm font-medium mt-2">Daftar duplikat (maks. 100 pertama):</div>
        <div class="max-h-56 overflow-auto border rounded p-2 text-xs">
            <ul class="list-disc pl-5">
                @foreach(array_slice($this->dupes, 0, 100) as $n)
                    <li>{{ $n }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="text-sm text-green-700">Tidak ada duplikat. Semua baris akan diperlakukan sebagai data baru (insert).</div>
    @endif
</div>
